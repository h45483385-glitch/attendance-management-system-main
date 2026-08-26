<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\BreakLog;
use App\Models\Employee;
use App\Models\MissedPunchRequest;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->query('employee_query');
        $employee = null;
        $monthlyLogs = [];
        $attendancePercentage = 0;

        if ($query) {
            $employee = Employee::where('id', $query)
                        ->orWhereRaw('LOWER(name) LIKE ?', ['%' . strtolower($query) . '%'])
                        ->first();

            if ($employee) {
                $rawLogs = Attendance::where('emp_id', $employee->id)->get();

                $groupedLogs = $rawLogs->groupBy(function($log) {
                    return Carbon::parse($log->attendance_date)->format('Y-m-d');
                });

                $finalLogs = collect();
                $presentDays = 0;
                $totalDays = $groupedLogs->count();
                $todayStr = Carbon::today()->format('Y-m-d');

                foreach ($groupedLogs as $date => $dayLogs) {
                    $dayLogs = $dayLogs->sortBy('attendance_time')->values();
                    $lastLog = $dayLogs->last();
                    
                    $totalMins = 0;
                    $hasMissingPunchOut = false;

                    foreach ($dayLogs as $log) {
                        $sessionMins = $log->worked_minutes;
                        if (!$sessionMins && $log->attendance_time) {
                            if ($log->check_out_time) {
                                $sessionMins = Carbon::parse($date . ' ' . $log->attendance_time)->diffInMinutes(Carbon::parse($date . ' ' . $log->check_out_time));
                            } elseif ($date === $todayStr) {
                                $sessionMins = Carbon::parse($date . ' ' . $log->attendance_time)->diffInMinutes(Carbon::now());
                            } else {
                                $hasMissingPunchOut = true;
                            }
                        }
                        $totalMins += (int)$sessionMins;
                    }

                    $hours = floor($totalMins / 60);
                    $mins = $totalMins % 60;

                    $status = 'Absent';

                    if ($date === $todayStr && empty($lastLog->check_out_time)) {
                        if ($lastLog->shift_status === 'On Break') {
                            $status = 'Break Time';
                        } else {
                            $status = 'In Progress';
                        }
                    } elseif ($date !== $todayStr && $hasMissingPunchOut) {
                        $status = 'Missing Punch';
                    } else {
                        if ($totalMins >= 480) {
                            $status = 'Present';
                        } elseif ($totalMins > 0) {
                            $status = 'Partial Shift';
                        }
                    }

                    $finalLogs->push((object)[
                        'date' => $date,
                        'net_hours' => "{$hours}h {$mins}m",
                        'status' => $status
                    ]);

                    if ($totalMins >= 480 || ($date === $todayStr && empty($lastLog->check_out_time))) {
                        $presentDays++;
                    }
                }

                $logs = $finalLogs->sortByDesc('date')->values();
                $attendancePercentage = $totalDays > 0 ? round(($presentDays / $totalDays) * 100) : 0;

                $monthlyLogs = $logs->groupBy(function($log) {
                    return Carbon::parse($log->date)->format('F Y');
                });
            }
        }

        return view('admin.attendance', compact('employee', 'monthlyLogs', 'attendancePercentage'));
    }

    public function indexLatetime()
    {
        $standardStartTime = '09:30:00'; 
        
        $allAttendances = Attendance::with('employee')
            ->whereNull('resolution_status')
            ->orderBy('attendance_date', 'desc')
            ->get();
            
        $latetimes = collect(); 

        $groupedAttendances = $allAttendances->groupBy(function($item) {
            return Carbon::parse($item->attendance_date)->format('Y-m-d');
        });

        foreach ($groupedAttendances as $date => $dayRecords) {
            $employeeFirstScans = $dayRecords->groupBy(function($item) {
                return (string) $item->emp_id;
            })->map(function ($employeeRecords) {
                return $employeeRecords->sortBy('attendance_time')->first();
            });

            foreach ($employeeFirstScans as $firstScan) {
                $timeIn = Carbon::parse($date . ' ' . $firstScan->attendance_time);
                $shiftStart = Carbon::parse($date . ' ' . $standardStartTime);

                if ($timeIn->greaterThan($shiftStart)) {
                    $lateMinutes = $shiftStart->diffInMinutes($timeIn);
                    
                    $hours = floor($lateMinutes / 60);
                    $mins = $lateMinutes % 60;
                    
                    $firstScan->formatted_late_time = $hours > 0 ? "{$hours}h {$mins}m" : "{$mins} mins";
                    $firstScan->clean_time_in = $timeIn->format('h:i A');
                    
                    if ($firstScan->check_out_time) {
                        $firstScan->clean_time_out = Carbon::parse($date . ' ' . $firstScan->check_out_time)->format('h:i A');
                    } else {
                        $firstScan->clean_time_out = 'Still Working';
                    }

                    $latetimes->push($firstScan); 
                }
            }
        }
        
        // Fetch Pending Missed Punch Requests for Tab 2
        $missedRequests = MissedPunchRequest::with('employee')
            ->where('status', 'Pending')
            ->orderBy('date', 'desc')
            ->get();

        return view('admin.latetime', compact('latetimes', 'missedRequests'));
    }

    public function waiveLate($id)
    {
        $attendance = Attendance::find($id);

        if ($attendance) {
            $attendance->resolution_status = 'Waived';
            $attendance->penalty_amount = 0;
            $attendance->save();

            return response()->json(['success' => true, 'message' => 'Late mark has been waived successfully!']);
        }

        return response()->json(['success' => false, 'message' => 'Record not found.']);
    }

    public function applyPenalty(Request $request, $id)
    {
        $attendance = Attendance::find($id);

        if ($attendance) {
            $attendance->resolution_status = 'Penalty Applied';
            $attendance->penalty_amount = $request->input('amount', 200.00); 
            $attendance->save();

            return response()->json(['success' => true, 'message' => 'Penalty applied and payroll updated!']);
        }

        return response()->json(['success' => false, 'message' => 'Record not found.']);
    }

    // 🚀 Missed Punch Approval / Rejection Logic
    public function resolveMissedScan(Request $request, $id)
    {
        $action = $request->input('action'); // 'approve' or 'reject'
        $missedRequest = MissedPunchRequest::find($id);

        if (!$missedRequest) {
            return response()->json(['success' => false, 'message' => 'Request not found.']);
        }

        if ($action === 'approve') {
            $missedRequest->status = 'Approved';
            $missedRequest->save();

            // Update or create attendance log with requested timeout
            Attendance::updateOrCreate(
                [
                    'emp_id' => $missedRequest->emp_id,
                    'attendance_date' => $missedRequest->date,
                ],
                [
                    'check_out_time' => $missedRequest->requested_timeout,
                    'shift_status' => 'Full Shift',
                ]
            );

            return response()->json(['success' => true, 'message' => 'Missed punch approved and attendance updated!']);
        } else {
            $missedRequest->status = 'Rejected';
            $missedRequest->save();

            return response()->json(['success' => true, 'message' => 'Missed punch request rejected.']);
        }
    }

    public function store(Request $request) 
    {
        $nextState = $request->input('state');
        $attendance = Attendance::find($request->input('attendance_id'));
        $emp_id = $request->input('emp_id');
        $now = Carbon::now();

        if ($nextState === 'On Break') {
            BreakLog::create([
                'attendance_id' => $attendance->id,
                'emp_id' => $emp_id,
                'break_start' => $now,
            ]);
            
            $attendance->shift_status = 'On Break';
            $attendance->save();
        }

        if ($nextState === 'Returned') {
            $openBreak = BreakLog::where('attendance_id', $attendance->id)
                ->whereNull('break_end')
                ->latest('break_start')
                ->first();

            if ($openBreak) {
                $openBreak->break_end = $now;
                $openBreak->duration_minutes = $now->diffInMinutes(Carbon::parse($openBreak->break_start));
                $openBreak->save();
            }
            
            $attendance->shift_status = 'Working';
            $attendance->save();
        }

        if ($nextState === 'Checked Out') {
            $attendance->check_out_time = $now->toTimeString();

            $openBreak = BreakLog::where('attendance_id', $attendance->id)
                ->whereNull('break_end')
                ->latest('break_start')
                ->first();

            if ($openBreak) {
                $openBreak->break_end = $now;
                $openBreak->duration_minutes = $now->diffInMinutes(Carbon::parse($openBreak->break_start));
                $openBreak->save();
            }

            $cleanDate = Carbon::parse($attendance->attendance_date)->format('Y-m-d');
            $exactCheckInString = $cleanDate . ' ' . $attendance->attendance_time;
            $checkIn = Carbon::parse($exactCheckInString);
            
            $totalMinutes = $checkIn->diffInMinutes($now);
            $breakMinutes = BreakLog::where('attendance_id', $attendance->id)->sum('duration_minutes');

            $attendance->worked_minutes = $totalMinutes - $breakMinutes;
            $attendance->shift_status = ($attendance->worked_minutes >= 480) ? 'Full Shift' : 'Partial Shift'; 
            
            $attendance->save();
        }

        return redirect()->back()->with('success', 'Attendance marked!');
    }
}