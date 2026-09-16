<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Employee;
use App\Models\Latetime;
use App\Models\Attendance;
use App\Models\BreakLog;
use App\Models\FingerDevices;
use Carbon\Carbon;

class AdminController extends Controller
{
    public function index()
    {
        // Use Carbon::today() as the single source of truth for "today".
        // This correctly resets at midnight regardless of any active shift end times.
        $today = Carbon::today();

        // 1. Present Today: Count UNIQUE employees who have an attendance_date
        //    strictly matching today's calendar date. whereDate() uses the DB
        //    DATE() function, so partial timestamps and shift overflows are ignored.
        $presentToday = Attendance::whereDate('attendance_date', $today)
            ->distinct()
            ->count('emp_id');

        // 2. Staff Lifecycle & Biometric Metrics
        $totalEmployees = Employee::count();
        $activeEmployees = Employee::where(function ($q) {
            $q->where('status', 'active')
              ->orWhere('status', 'Active')
              ->orWhereNull('status');
        })->count();
        $deactivatedEmployees = Employee::where(function ($q) {
            $q->where('status', 'inactive')
              ->orWhere('status', 'Inactive')
              ->orWhere('status', 'deactivated')
              ->orWhere('status', 'Deactivated');
        })->count();
        $faceEnrolledCount = Employee::where('face_enrolled', true)->count();
        $fingerprintEnrolledCount = Employee::where('fingerprint_enrolled', true)->count();

        // 3. Absent Today = total roster minus those who have already checked in today
        $absentToday = max(0, $totalEmployees - $presentToday);

        // 4. Late Arrivals: Unique employees whose first scan is marked late (status = 0)
        $lateArrivals = Attendance::whereDate('attendance_date', $today)
            ->where('status', '0')
            ->distinct()
            ->count('emp_id');

        // 5. Currently On Break: Active break sessions started today with no end time
        $onBreak = BreakLog::whereDate('break_start', $today)
            ->whereNull('break_end')
            ->distinct()
            ->count('attendance_id');

        // 6. Average On-Time %
        $ontimeEmp = Attendance::whereDate('attendance_date', $today)
            ->where('status', '1')
            ->distinct()
            ->count('emp_id');
        $onTimePercentage = $presentToday > 0
            ? round(($ontimeEmp / $presentToday) * 100)
            : 0;

        // Off-Time / Checked-out for the day
        $offTimeEmp = Attendance::whereDate('attendance_date', $today)
            ->whereNotNull('check_out_time')
            ->where('state', 0)
            ->distinct()
            ->count('emp_id');

        // 7. Fingerprint Hardware Status Panel Data
        $fingerDevices = FingerDevices::all();
        $totalFingerDevices = $fingerDevices->count();
        $connectedFingerDevices = 0;

        foreach ($fingerDevices as $device) {
            if ($device->status === 'Blocked' || $device->status === 'Inactive') {
                $device->is_online = false;
                continue;
            }

            $fp = @fsockopen($device->ip, 4370, $errno, $errstr, 0.4);
            if ($fp) {
                $connectedFingerDevices++;
                fclose($fp);
                $device->is_online = true;
            } else {
                $device->is_online = ($device->status === 'Online');
                if ($device->is_online) {
                    $connectedFingerDevices++;
                }
            }
        }
        $offlineFingerDevices = max(0, $totalFingerDevices - $connectedFingerDevices);
        $devicesOnline = $connectedFingerDevices;

        // 8. Today's live attendance log for the table widget
        $todayLogs = Attendance::with(['employee', 'activeBreakLog'])
            ->whereDate('attendance_date', $today)
            ->get();

        // Chart data: last 7 days present / absent
        $chartDays    = [];
        $chartPresent = [];
        $chartAbsent  = [];

        $sevenDaysAgo = Carbon::today()->subDays(6)->toDateString();
        $weeklyCounts = Attendance::where('attendance_date', '>=', $sevenDaysAgo)
            ->selectRaw('attendance_date, count(distinct emp_id) as total_present')
            ->groupBy('attendance_date')
            ->pluck('total_present', 'attendance_date')
            ->toArray();

        for ($i = 6; $i >= 0; $i--) {
            $dateObj = Carbon::today()->subDays($i);
            $dateStr = $dateObj->toDateString();

            $present = 0;
            foreach ($weeklyCounts as $wDate => $wCount) {
                if (date('Y-m-d', strtotime($wDate)) === $dateStr) {
                    $present = $wCount;
                    break;
                }
            }

            $chartDays[]    = $dateObj->format('M d');
            $chartPresent[] = $present;
            $chartAbsent[]  = max(0, $totalEmployees - $present);
        }

        // Biometric enrollment breakdown for the donut chart
        $statusActive  = Employee::where('face_enrolled', true)
            ->orWhere('fingerprint_enrolled', true)
            ->count();
        $statusPending = max(0, $totalEmployees - $statusActive);

        // Recent additions and audit activity
        $recentEmployees = Employee::orderBy('created_at', 'desc')->limit(5)->get();
        $recentActivities = \Illuminate\Support\Facades\DB::table('audit_logs')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('admin.index', compact(
            'presentToday',
            'totalEmployees',
            'activeEmployees',
            'deactivatedEmployees',
            'faceEnrolledCount',
            'fingerprintEnrolledCount',
            'fingerDevices',
            'totalFingerDevices',
            'connectedFingerDevices',
            'offlineFingerDevices',
            'absentToday',
            'lateArrivals',
            'onBreak',
            'ontimeEmp',
            'offTimeEmp',
            'onTimePercentage',
            'devicesOnline',
            'todayLogs',
            'chartDays',
            'chartPresent',
            'chartAbsent',
            'statusActive',
            'statusPending',
            'recentEmployees',
            'recentActivities'
        ));
    }
}