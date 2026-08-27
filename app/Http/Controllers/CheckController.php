<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Leave;
use Carbon\Carbon;

class CheckController extends Controller
{
    public function index()
    {
        $attendances = Attendance::with('employee')
            ->whereDate('attendance_date', Carbon::today())
            ->orderBy('attendance_time', 'asc')
            ->get();

        $dailyAttendances = $attendances->groupBy('emp_id')->map(function ($records) {
            $firstRecord = $records->first();
            $latestRecord = $records->sortByDesc('updated_at')->first();

            $totalMins = 0;
            $isCurrentlyWorking = false;

            // Calculate total minutes across all punches for today
            foreach($records as $log) {
                $cleanDate = Carbon::parse($log->attendance_date)->format('Y-m-d');
                $checkIn = Carbon::parse($cleanDate . ' ' . $log->attendance_time);
                
                if ($log->check_out_time) {
                    $checkOut = Carbon::parse($cleanDate . ' ' . $log->check_out_time);
                    $totalMins += $checkIn->diffInMinutes($checkOut);
                } else {
                    $totalMins += $checkIn->diffInMinutes(Carbon::now());
                    $isCurrentlyWorking = true;
                }
            }

            // DYNAMIC STATUS LOGIC FOR DAILY SHEET
            if ($latestRecord->shift_status === 'On Break') {
                $status = 'On Break';
            } elseif ($isCurrentlyWorking) {
                $status = 'In Progress';
            } elseif ($totalMins >= 480) {
                $status = 'Present';
            } elseif ($totalMins > 0) {
                $status = 'Partial Shift';
            } else {
                $status = 'Absent';
            }

            $firstRecord->time_in = $firstRecord->attendance_time;
            $firstRecord->time_out = $latestRecord->check_out_time;
            $firstRecord->status = $status;
            $firstRecord->employee_id = $firstRecord->emp_id;

            return $firstRecord;
        })->values();

        return view('admin.check', compact('dailyAttendances'));
    }

    public function CheckStore(Request $request)
    {
        // ATTENDANCE BLOCK
        if (isset($request->attd)) {
            foreach ($request->attd as $keys => $values) {
                foreach ($values as $key => $value) {
                    if ($employee = Employee::whereId($key)->first()) {
                        if (
                            !Attendance::whereAttendance_date($keys)
                                ->whereEmp_id($key)
                                ->whereType(0)
                                ->first()
                        ) {
                            $data = new Attendance();
                            $data->emp_id = $key;
                            
                            $schedule = $employee->schedules->first();
                            $data->attendance_time = $schedule ? date('H:i:s', strtotime($schedule->time_in)) : '09:00:00';
                            $data->attendance_date = $keys;
                            
                            $data->save();
                        }
                    }
                }
            }
        }
        
        // LEAVE BLOCK
        if (isset($request->leave)) {
            foreach ($request->leave as $keys => $values) {
                foreach ($values as $key => $value) {
                    if ($employee = Employee::whereId($key)->first()) {
                        if (
                            !Leave::whereLeave_date($keys)
                                ->whereEmp_id($key)
                                ->whereType(1)
                                ->first()
                        ) {
                            $data = new Leave();
                            $data->emp_id = $key;
                            
                            $schedule = $employee->schedules->first();
                            $data->leave_time = $schedule ? $schedule->time_out : '18:00:00';
                            $data->leave_date = $keys;
                            
                            $data->save();
                        }
                    }
                }
            }
        }
        
        flash()->success('Success', 'You have successfully submitted the attendance!');
        return back();
    }
    
    public function sheetReport()
    {
        $todayStr = Carbon::today()->format('Y-m-d');
        $employees = Employee::all();
        
        // 🚀 N+1 Query Fix: Get all present employee IDs for today in a single query
        $todayPresentEmpIds = Attendance::whereDate('attendance_date', $todayStr)
            ->pluck('emp_id')
            ->toArray();

        return view('admin.sheet-report', compact('employees', 'todayPresentEmpIds'));
    }
}