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
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();

        // Include today's punches and open overnight punches from yesterday
        $attendances = Attendance::with('employee.schedules')
            ->where(function($q) use ($today, $yesterday) {
                $q->whereDate('attendance_date', $today)
                  ->orWhere(function($sub) use ($yesterday) {
                      // Yesterday's records that were checked in after 18:00 (night shifts)
                      $sub->whereDate('attendance_date', $yesterday)
                          ->where('attendance_time', '>=', '18:00:00');
                  });
            })
            ->orderBy('attendance_date', 'asc')
            ->orderBy('attendance_time', 'asc')
            ->get();

        $dailyAttendances = $attendances->groupBy('emp_id')->map(function ($records) use ($today) {
            $firstRecord = $records->first();
            $latestRecord = $records->sortByDesc('updated_at')->first();

            $totalMins = 0;
            $isCurrentlyWorking = false;

            // Calculate total minutes across all punches
            foreach($records as $log) {
                $cleanDate = Carbon::parse($log->attendance_date)->format('Y-m-d');
                $checkIn = Carbon::parse($cleanDate . ' ' . $log->attendance_time);
                
                if ($log->check_out_time) {
                    $checkOut = Carbon::parse($cleanDate . ' ' . $log->check_out_time);
                    if ($checkOut->lessThan($checkIn)) {
                        // Rollover across midnight
                        $checkOut->addDay();
                    }
                    $totalMins += $checkIn->diffInMinutes($checkOut);
                } else {
                    $totalMins += $checkIn->diffInMinutes(Carbon::now());
                    $isCurrentlyWorking = true;
                }
            }

            $setting = \App\Models\Setting::first();
            $fullDayMinutes = ($setting && $setting->min_full_day_hours) ? ($setting->min_full_day_hours * 60) : 480;

            // DYNAMIC STATUS LOGIC FOR DAILY SHEET
            if ($latestRecord->shift_status === 'On Break') {
                $status = 'On Break';
            } elseif ($isCurrentlyWorking) {
                $status = 'In Progress';
            } elseif ($totalMins >= $fullDayMinutes) {
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
                            $now = Carbon::now();
                            $data = new Attendance();
                            $data->emp_id = $key;
                            
                            $data->attendance_time = $now->toTimeString(); // Record exact time of manual check-in
                            $data->attendance_date = $keys;
                            
                            $schedule = $employee->schedules->first();
                            $employeeStartTime = $schedule ? $schedule->time_in : '09:30:00';
                            $setting = \App\Models\Setting::first();
                            $gracePeriod = $setting ? (int)$setting->grace_period : 10;

                            $shiftStart = Carbon::parse($keys . ' ' . $employeeStartTime);
                            $lateThreshold = $shiftStart->copy()->addMinutes($gracePeriod);
                            $timeIn = Carbon::parse($keys . ' ' . $data->attendance_time);
                            
                            $isLate = ($timeIn->greaterThan($lateThreshold)) ? 0 : 1;
                            $data->status = $isLate;
                            
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
                        // Annual Leave Quota Enforcement from Setting
                        $setting = \App\Models\Setting::first();
                        $annualQuota = $setting ? ($setting->casual_leaves + $setting->medical_leaves) : 18;
                        $leaveYear = Carbon::parse($keys)->year;

                        $usedLeavesThisYear = Leave::where('emp_id', $key)
                            ->whereYear('leave_date', $leaveYear)
                            ->count();

                        if ($usedLeavesThisYear >= $annualQuota) {
                            flash()->error('Quota Exceeded', "Leave rejected for {$employee->name}: Annual leave quota ({$annualQuota} days) has already been exhausted.");
                            continue;
                        }

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