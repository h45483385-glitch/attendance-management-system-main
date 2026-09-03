<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CameraHealthCheckService
{
    protected $timeout = 2; // seconds

    /**
     * Check health for all cameras in the database.
     */
    public function checkAllCameras()
    {
        $cameras = DB::table('cameras')->get();
        foreach ($cameras as $camera) {
            $this->checkHealth($camera);
        }
    }

    /**
     * Perform a health check for a specific camera.
     */
    public function checkHealth($camera)
    {
        $status = 'UNKNOWN';
        $errorMessage = null;
        $latencyMs = null;

        $startTime = microtime(true);

        try {
            // Configurable checks: we try port 80 or 554 as a default TCP check
            // For enterprise, we might use HTTP if it's an IP camera
            // Here we use fsockopen as a fast ping
            $ip = parse_url($camera->ip, PHP_URL_HOST) ?? $camera->ip;
            $port = parse_url($camera->ip, PHP_URL_PORT) ?? 80; // default 80

            // Strip http:// or rtsp:// if it exists in the IP string
            $ip = preg_replace('#^https?://#', '', $ip);
            $ip = preg_replace('#^rtsp://#', '', $ip);

            $fp = @fsockopen($ip, $port, $errno, $errstr, $this->timeout);

            if ($fp) {
                fclose($fp);
                $latencyMs = (int) ((microtime(true) - $startTime) * 1000);
                $status = 'ONLINE';
            } else {
                $status = 'OFFLINE';
                $errorMessage = $errstr ?: 'Connection Timeout';
            }
        } catch (\Exception $e) {
            $status = 'OFFLINE';
            $errorMessage = $e->getMessage();
        }

        $this->updateStatus($camera, $status, $latencyMs, $errorMessage);
    }

    /**
     * State transition and logging.
     */
    protected function updateStatus($camera, $newStatus, $latencyMs, $errorMessage)
    {
        // Get the latest log for this camera
        $lastLog = DB::table('camera_health_logs')
            ->where('camera_id', $camera->camera_id)
            ->orderBy('id', 'desc')
            ->first();

        $consecutiveFailures = 0;
        $consecutiveSuccesses = 0;

        if ($lastLog) {
            $consecutiveFailures = $lastLog->consecutive_failures;
            $consecutiveSuccesses = $lastLog->consecutive_successes;
        }

        // State Machine logic
        if ($newStatus === 'ONLINE') {
            $consecutiveSuccesses++;
            $consecutiveFailures = 0;
        } else {
            $consecutiveFailures++;
            $consecutiveSuccesses = 0;
            
            // If it failed just once or twice, mark DEGRADED
            // If it failed >= 3 times, mark OFFLINE
            if ($consecutiveFailures < 3) {
                $newStatus = 'DEGRADED';
            } else {
                $newStatus = 'OFFLINE';
            }
        }

        // Update cameras table
        DB::table('cameras')->where('id', $camera->id)->update([
            'status' => $newStatus,
            'last_seen' => $newStatus === 'ONLINE' ? now() : $camera->last_seen,
            'updated_at' => now(),
        ]);

        // Insert Health Log
        DB::table('camera_health_logs')->insert([
            'camera_id' => $camera->camera_id,
            'status' => $newStatus,
            'latency_ms' => $latencyMs,
            'consecutive_failures' => $consecutiveFailures,
            'consecutive_successes' => $consecutiveSuccesses,
            'error_message' => $errorMessage,
            'last_checked_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // Use Audit Logger for significant transitions
        if ($lastLog && $lastLog->status !== $newStatus && in_array($newStatus, ['ONLINE', 'OFFLINE'])) {
            AuditLogger::log(
                "CAMERA_STATE_CHANGE_{$newStatus}",
                'Device Management',
                "Camera {$camera->camera_name} (ID: {$camera->camera_id}) transitioned to {$newStatus}",
                'Camera',
                $camera->id,
                ['status' => $lastLog->status],
                ['status' => $newStatus]
            );
        }
    }
}
