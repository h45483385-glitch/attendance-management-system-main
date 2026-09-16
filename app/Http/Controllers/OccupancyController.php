<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class OccupancyController extends Controller
{
    /**
     * Display live office occupancy tracker: who is inside vs who has left.
     */
    public function index(Request $request)
    {
        $today = Carbon::today()->toDateString();
        $employees = Employee::orderBy('name')->get();

        // Retrieve latest punch today for every employee
        $attendances = DB::table('attendances')
            ->where('attendance_date', $today)
            ->orderBy('id', 'desc')
            ->get()
            ->groupBy('emp_id')
            ->map(function ($punches) {
                return $punches->first();
            });

        // Check active open breaks today
        $openBreaks = DB::table('break_logs')
            ->whereDate('created_at', $today)
            ->whereNull('break_end')
            ->pluck('emp_id')
            ->toArray();

        $inside = [];
        $onBreak = [];
        $left = [];
        $notPresent = [];

        foreach ($employees as $emp) {
            $latest = $attendances->get($emp->id);

            if (!$latest) {
                $notPresent[] = [
                    'employee'   => $emp,
                    'status'     => 'Not Punched Today',
                    'last_seen'  => 'N/A',
                    'duration'   => '0 mins',
                ];
            } else {
                $inTime = Carbon::parse($latest->attendance_date . ' ' . $latest->attendance_time);

                if (in_array($emp->id, $openBreaks)) {
                    $onBreak[] = [
                        'employee'   => $emp,
                        'status'     => 'On Break',
                        'last_seen'  => $latest->attendance_time,
                        'duration'   => $inTime->diffForHumans(null, true),
                    ];
                } elseif ($latest->state == 1) {
                    $inside[] = [
                        'employee'   => $emp,
                        'status'     => 'Inside Office',
                        'last_seen'  => $latest->attendance_time,
                        'duration'   => $inTime->diffForHumans(null, true),
                    ];
                } else {
                    $outTime = $latest->check_out_time ? Carbon::parse($latest->attendance_date . ' ' . $latest->check_out_time)->format('h:i A') : 'Checked Out';
                    $left[] = [
                        'employee'   => $emp,
                        'status'     => 'Left Office',
                        'last_seen'  => $outTime,
                        'duration'   => $latest->check_out_time ? Carbon::parse($latest->attendance_date . ' ' . $latest->check_out_time)->diffForHumans() : 'N/A',
                    ];
                }
            }
        }

        $metrics = [
            'total'       => $employees->count(),
            'inside'      => count($inside),
            'on_break'    => count($onBreak),
            'left'        => count($left),
            'not_present' => count($notPresent),
        ];

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'metrics' => $metrics,
                'inside'  => $inside,
                'onBreak' => $onBreak,
                'left'    => $left,
            ]);
        }

        return view('admin.occupancy', compact('metrics', 'inside', 'onBreak', 'left', 'notPresent'));
    }
}
