<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class SecurityDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:security.view');
    }

    public function index()
    {
        // 1. Users metrics
        $totalUsers = User::count();
        $activeUsers = User::where('status', 'Active')->count();
        $inactiveUsers = User::where('status', 'Inactive')->count();

        // 2. Devices metrics
        $totalDevices = DB::table('finger_devices')->count();
        $blockedDevices = DB::table('finger_devices')->where('status', 'Blocked')->count();
        $inactiveDevices = DB::table('finger_devices')->where('status', 'Inactive')->count();

        // 3. Cameras metrics
        $totalCameras = DB::table('cameras')->count();
        $disconnectedCameras = DB::table('cameras')->where('status', 'Disconnected')->count();

        // 4. Failed logins and Lockouts
        $failedLoginsSum = User::sum('failed_logins');
        $auditFailedLogins = DB::table('audit_logs')->where('action', 'LOGIN_FAILED')->count();
        $lockoutsCount = DB::table('audit_logs')->where('action', 'LOGIN_LOCKOUT')->count();

        // 5. Recent Security Events
        $recentEvents = DB::table('audit_logs')
            ->whereIn('action', ['LOGIN_FAILED', 'LOGIN_LOCKOUT', 'SECURITY_SETTING_CHANGED', 'PERMISSION_CHANGED', 'DEVICE_BLOCKED'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('admin.security.dashboard', compact(
            'totalUsers', 'activeUsers', 'inactiveUsers',
            'totalDevices', 'blockedDevices', 'inactiveDevices',
            'totalCameras', 'disconnectedCameras',
            'failedLoginsSum', 'auditFailedLogins', 'lockoutsCount',
            'recentEvents'
        ));
    }
}
