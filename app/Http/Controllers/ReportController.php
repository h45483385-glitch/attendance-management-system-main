<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\DailyPayLog;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:reports.view');
    }

    public function index(Request $request)
    {
        $type = $request->input('type', 'daily');
        $startDate = $request->input('start_date', now()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());
        $employeeId = $request->input('employee_id');
        $department = $request->input('department');

        // Set default date range if weekly/monthly is chosen
        if (!$request->has('start_date')) {
            if ($type === 'weekly') {
                $startDate = now()->subDays(6)->toDateString();
            } elseif ($type === 'monthly') {
                $startDate = now()->startOfMonth()->toDateString();
                $endDate = now()->endOfMonth()->toDateString();
            }
        }

        // Fetch support filters
        $employees = Employee::orderBy('name')->get();
        $departments = Employee::whereNotNull('department')->where('department', '!=', '')->distinct()->pluck('department');

        $data = $this->generateReportData($type, $startDate, $endDate, $employeeId, $department);

        // Check if export is requested
        if ($request->input('export') === 'csv') {
            return $this->exportCSV($type, $data);
        }

        if ($request->input('export') === 'pdf') {
            return view('admin.reports.print', [
                'type' => $type,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'data' => $data
            ]);
        }

        return view('admin.reports.index', [
            'type' => $type,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'employeeId' => $employeeId,
            'department' => $department,
            'employees' => $employees,
            'departments' => $departments,
            'data' => $data
        ]);
    }

    private function generateReportData($type, $startDate, $endDate, $employeeId = null, $department = null)
    {
        $query = Attendance::with('employee');

        // Apply general date filters
        if ($type === 'daily') {
            $query->whereDate('attendance_date', $startDate);
        } else {
            $query->whereBetween('attendance_date', [$startDate, $endDate]);
        }

        // Apply employee filter
        if ($employeeId) {
            $query->where('emp_id', $employeeId);
        }

        // Apply department filter
        if ($department) {
            $query->whereHas('employee', function($q) use ($department) {
                $q->where('department', $department);
            });
        }

        switch ($type) {
            case 'daily':
            case 'weekly':
            case 'monthly':
            case 'employee':
                return $query->orderBy('attendance_date', 'desc')
                             ->orderBy('attendance_time', 'asc')
                             ->get();

            case 'department':
                return $query->orderBy('attendance_date', 'desc')
                             ->get()
                             ->groupBy(function($item) {
                                 return $item->employee->department ?? 'General';
                             });

            case 'late':
                // status = 0 is late
                return $query->where('status', 0)
                             ->orderBy('attendance_date', 'desc')
                             ->get();

            case 'absence':
                // To get 100% accurate absence, we find dates in range and check which employees did not check in.
                $employeesList = Employee::query();
                if ($employeeId) {
                    $employeesList->where('id', $employeeId);
                }
                if ($department) {
                    $employeesList->where('department', $department);
                }
                $employeesList = $employeesList->get();

                $period = CarbonPeriod::create($startDate, $endDate);
                $absences = collect();

                foreach ($period as $date) {
                    $dateStr = $date->toDateString();
                    
                    // Skip Sundays/Weekends if needed, but for absolute accuracy we check all days
                    $presentEmpIds = Attendance::whereDate('attendance_date', $dateStr)->pluck('emp_id')->toArray();

                    foreach ($employeesList as $emp) {
                        if (!in_array($emp->id, $presentEmpIds)) {
                            $absences->push((object)[
                                'date' => $dateStr,
                                'employee' => $emp,
                                'status' => 'Absent'
                            ]);
                        }
                    }
                }
                return $absences->sortByDesc('date');

            case 'overtime':
                $payQuery = DailyPayLog::with('employee')
                                        ->where('overtime_minutes', '>', 0)
                                        ->whereIn('overtime_status', ['approved', 'pending_approval']);
                
                if ($type === 'daily') {
                    $payQuery->whereDate('date', $startDate);
                } else {
                    $payQuery->whereBetween('date', [$startDate, $endDate]);
                }

                if ($employeeId) {
                    $payQuery->where('employee_id', $employeeId);
                }

                if ($department) {
                    $payQuery->whereHas('employee', function($q) use ($department) {
                        $q->where('department', $department);
                    });
                }

                return $payQuery->orderBy('date', 'desc')->get();

            default:
                return collect();
        }
    }

    private function exportCSV($type, $data)
    {
        $filename = "Attendance_Report_" . ucfirst($type) . "_" . now()->format('YmdHis') . ".csv";
        
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use ($type, $data) {
            $file = fopen('php://output', 'w');

            if ($type === 'overtime') {
                // Headers for Overtime Report
                fputcsv($file, ['Employee ID', 'Employee Name', 'Department', 'Date', 'Worked Minutes', 'Overtime Minutes', 'Overtime Pay', 'Status']);
                foreach ($data as $row) {
                    fputcsv($file, [
                        $row->employee->id,
                        $row->employee->name,
                        $row->employee->department ?? 'General',
                        $row->date,
                        $row->worked_minutes,
                        $row->overtime_minutes,
                        $row->overtime_pay,
                        ucfirst($row->overtime_status)
                    ]);
                }
            } elseif ($type === 'absence') {
                // Headers for Absence Report
                fputcsv($file, ['Date', 'Employee ID', 'Employee Name', 'Department', 'Status']);
                foreach ($data as $row) {
                    fputcsv($file, [
                        $row->date,
                        $row->employee->id,
                        $row->employee->name,
                        $row->employee->department ?? 'General',
                        $row->status
                    ]);
                }
            } else {
                // Headers for General Attendance Reports
                fputcsv($file, ['Date', 'Employee ID', 'Employee Name', 'Department', 'Check In', 'Check Out', 'Worked Minutes', 'Status']);
                
                if ($type === 'department') {
                    foreach ($data as $dept => $rows) {
                        foreach ($rows as $row) {
                            fputcsv($file, [
                                $row->attendance_date,
                                $row->employee->id,
                                $row->employee->name,
                                $dept,
                                $row->attendance_time,
                                $row->check_out_time ?? 'N/A',
                                $row->worked_minutes ?? 0,
                                $row->status == 1 ? 'On Time' : 'Late'
                            ]);
                        }
                    }
                } else {
                    foreach ($data as $row) {
                        fputcsv($file, [
                            $row->attendance_date,
                            $row->employee->id,
                            $row->employee->name,
                            $row->employee->department ?? 'General',
                            $row->attendance_time,
                            $row->check_out_time ?? 'N/A',
                            $row->worked_minutes ?? 0,
                            $row->status == 1 ? 'On Time' : 'Late'
                        ]);
                    }
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
