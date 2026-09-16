<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class FaceMismatchAlertService
{
    /**
     * Trigger security alarm / notification on biometric face mismatch
     */
    public static function triggerMismatchAlert(string $details, ?string $ip = null, ?int $suspectedEmpId = null)
    {
        $clientIp = $ip ?: request()->ip();

        // 1. Log to system application log
        Log::warning("[FACE MISMATCH ALERT] {$details} from IP: {$clientIp}");

        // 2. Insert into Audit Logs table for security dashboard visibility
        AuditLogger::log(
            'FACE_MISMATCH_ALARM',
            'Security',
            "Biometric Face Mismatch Alert: {$details} (Origin IP: {$clientIp})",
            User::class,
            $suspectedEmpId
        );

        // 3. Mark in security alerts or notify admin accounts
        try {
            $adminUsers = User::where('role', 'admin')->get();
            foreach ($adminUsers as $admin) {
                // Future notification dispatch (e.g. database or mail notification)
            }
        } catch (\Exception $e) {
            Log::error("Failed to notify admins: " . $e->getMessage());
        }
    }
}
