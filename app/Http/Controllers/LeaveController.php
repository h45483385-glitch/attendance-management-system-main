<?php

namespace App\Http\Controllers;

use DateTime;
use App\Models\User;
use App\Models\Employee;
use App\Models\Overtime;
use App\Models\SalaryMaster;
use App\Models\Leave;
use Illuminate\Http\Request;

class LeaveController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // 🔒 RBAC DATA SCOPE: Employee may only see their own leave records.
        // Admin sees all leave requests across the organisation.
        if ($user->hasRole('employee')) {
            $linkedEmployee = \Illuminate\Support\Facades\Schema::hasColumn('employees', 'user_id')
                ? (Employee::where('user_id', $user->id)->first() ?: Employee::where('email', $user->email)->first())
                : Employee::where('email', $user->email)->first();
            $leaves = $linkedEmployee
                ? Leave::where('emp_id', $linkedEmployee->id)->get()
                : collect();
        } else {
            $leaves = Leave::all();
        }

        return view('admin.leave')->with(['leaves' => $leaves]);
    }

    // Overtime Controller Logic separating Individual Wallet vs Global Admin Approval
    public function indexOvertime(Request $request)
    {
        // தனிநபர் வாலட் பார்வையாக இருந்தால் (employee_id வந்தால்)
        if ($request->has('employee_id') && !empty($request->employee_id)) {
            $employeeId = $request->employee_id;
            $employee = Employee::with('schedules')->findOrFail($employeeId);
            
            // அந்த ஒரு குறிப்பிட்ட எம்ப்ளாயியின் ஓவர்டைம் மட்டும்
            $overtimes = Overtime::where('emp_id', $employeeId)
                ->orderBy('overtime_date', 'desc')
                ->get();

            return view('admin.employee_overtime_wallet', compact('overtimes', 'employee'));
        }

        // மெனுவிலிருந்து வரும் அட்மின் குளோபல் அப்ரூவல் பக்கம் (அனைத்து ஊழியர்களும்)
        $overtimes = Overtime::with('employee.schedules')
            ->orderBy('overtime_date', 'desc')
            ->get();

        return view('admin.overtime', compact('overtimes'));
    }

    // அட்மின் அப்ரூவல் செய்யும் போது பேரோலில் சம்பளத்தை ஆட் செய்வது
    public function approveOvertime($id)
    {
        $overtime = Overtime::findOrFail($id);
        $overtime->status = 'Approved';
        
        // Pay Report / Salary Master-ல் இருந்து மணிநேரச் சம்பளத்தைக் கணக்கிட்டு ஆட் செய்தல்
        $employee = Employee::with('salaryMaster')->find($overtime->emp_id);
        if ($employee && $employee->salaryMaster) {
            $basic = $employee->salaryMaster->basic ?? 0;
            $hourlyRate = $basic > 0 ? ($basic / 160) : 0; // மாதத்திற்கு 160 மணி நேரம் என வைத்துக்கொள்வோம்

            $parts = explode(':', $overtime->duration);
            $hours = isset($parts[0]) ? (int)$parts[0] : 0;
            $minutes = isset($parts[1]) ? (int)$parts[1] : 0;
            $totalHours = $hours + ($minutes / 60);

            // ஓவர்டைம் தொகையைக் கணக்கிட்டு சேமித்தல்
            $overtime->amount = round($totalHours * $hourlyRate, 2);
        }

        $overtime->save();

        flash()->success('Success', 'Overtime approved and successfully added to payroll calculation!');
        return back();
    }

    public function rejectOvertime($id)
    {
        $overtime = Overtime::findOrFail($id);
        $overtime->status = 'Rejected';
        $overtime->save();

        flash()->success('Success', 'Overtime request has been rejected.');
        return back();
    }

    public static function overTimeDevice($att_dateTime, Employee $employee)
    {
        if ($employee->schedules->first()) {
            $attendance_time = new DateTime($att_dateTime);
            $checkout = new DateTime($employee->schedules->first()->time_out);
            
            if ($attendance_time > $checkout) {
                $difference = $checkout->diff($attendance_time)->format('%H:%I:%S');

                $overtime = new Overtime();
                $overtime->emp_id = $employee->id;
                $overtime->duration = $difference;
                $overtime->overtime_date = date('Y-m-d', strtotime($att_dateTime));
                $overtime->status = 'Pending'; // புதிய கோரிக்கை Pending-ல் விழும்
                $overtime->save();
            }
        }
    }
}