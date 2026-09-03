<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\CameraHealthCheckService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class CameraHealthCheckServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $healthService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->healthService = new CameraHealthCheckService();
    }

    public function test_camera_state_transitions_to_offline_after_failures()
    {
        $cameraId = DB::table('cameras')->insertGetId([
            'camera_name' => 'Test Cam',
            'camera_id' => 'CAM-001',
            'ip' => '256.256.256.256', // Invalid IP to force failure
            'location' => 'Gate 1',
            'camera_type' => 'IP',
            'status' => 'ONLINE'
        ]);

        $camera = DB::table('cameras')->where('id', $cameraId)->first();

        // 1st failure -> DEGRADED
        $this->healthService->checkHealth($camera);
        $updatedCamera = DB::table('cameras')->where('id', $cameraId)->first();
        $this->assertEquals('DEGRADED', $updatedCamera->status);

        // 2nd failure -> DEGRADED
        $this->healthService->checkHealth($updatedCamera);
        
        // 3rd failure -> OFFLINE
        $updatedCamera = DB::table('cameras')->where('id', $cameraId)->first();
        $this->healthService->checkHealth($updatedCamera);

        $finalCamera = DB::table('cameras')->where('id', $cameraId)->first();
        $this->assertEquals('OFFLINE', $finalCamera->status);
        
        // Check logs
        $logs = DB::table('camera_health_logs')->where('camera_id', 'CAM-001')->count();
        $this->assertEquals(3, $logs);
    }
}
