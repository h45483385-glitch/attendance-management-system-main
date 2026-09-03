<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class AuditLogger
{
    /**
     * Log a security or admin audit trail event immutably.
     *
     * @param string $action       e.g., 'LOGIN', 'USER_CREATED'
     * @param string $module       e.g., 'User Management', 'Security'
     * @param string $description  Detailed context of what happened
     * @param string|null $targetType Model class or target type
     * @param string|null $targetId Unique ID of target
     * @param array|null $before   Data array before change
     * @param array|null $after    Data array after change
     */
    public static function log($action, $module, $description, $targetType = null, $targetId = null, $before = null, $after = null)
    {
        $user = auth()->user();
        
        try {
            DB::table('audit_logs')->insert([
                'actor_id' => $user ? $user->id : null,
                'actor_name' => $user ? $user->name : 'System/Guest',
                'action' => $action,
                'module' => $module,
                'target_type' => $targetType,
                'target_id' => $targetId,
                'description' => $description,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'before_data' => $before ? json_encode($before) : null,
                'after_data' => $after ? json_encode($after) : null,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            // Clear cached filters to ensure index dropdowns remain up-to-date
            cache()->forget('audit_actors');
            cache()->forget('audit_actions');
            cache()->forget('audit_modules');
        } catch (\Exception $e) {
            \Log::error("Failed writing audit trail: " . $e->getMessage());
        }
    }
}
