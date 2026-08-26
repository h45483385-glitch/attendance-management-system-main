<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\SalaryMaster;
use App\Models\Attendance;
use Carbon\Carbon;

class PayReportController extends Controller
{
    public function index()
    {
        // 1. அனைத்து எம்ப்ளாயிக்களையும் மற்றும் சலரி மாஸ்டர் டேட்டாவையும் பெறுதல்
        $employees = Employee::all();
        
        // 2. டிபார்ட்மென்ட் வாரியாக எம்ப்ளாயிக்களை க்ரூப் செய்ய SalaryMaster உடன் இணைத்தல்
        $departments = SalaryMaster::select('department')->distinct()->pluck('department');

        return view('pay-report.index')->with([
            'employees' => $employees,
            'departments' => $departments
        ]);
    }

    // 🚀 JS-ல் இருந்து fetchPayData(id) என்று அழைக்கும்போது துல்லியமான சம்பளம் மற்றும் பெனால்டி டேட்டாவை அனுப்பும் API
    public function fetchPayData($id)
    {
        $employee = Employee::find($id);
        
        if(!$employee) {
            return response()->json(['success' => false, 'message' => 'Employee not found']);
        }

        // 1. கேஸ்-சென்சிடிவ் பிரச்சனை வராமல் இருக்க designation-ஐ lowercase செய்து தேடுதல்
        $salaryMaster = SalaryMaster::whereRaw('LOWER(designation) = ?', [strtolower(trim($employee->position))])->first();
        
        // 2. மாஸ்டரில் இருந்தால் அந்தச் சம்பளம், இல்லையென்றால் டிஃபால்ட் சம்பளம்
        $baseSalary = $salaryMaster ? $salaryMaster->current_base_salary : 0;
        
        // SalaryMaster-ல் அந்தப் பதவி விடுபட்டிருந்தாலும் ஆட்டோமேட்டிக்காக கிரியேட் செய்யும் பாதுகாப்பு லாஜிக்
        if (!$salaryMaster && !empty($employee->position)) {
            $salaryMaster = SalaryMaster::firstOrCreate(
                ['designation' => $employee->position],
                [
                    'department' => 'Engineering', 
                    'current_base_salary' => (str_contains(strtolower($employee->position), 'intern')) ? 15000 : 30000, 
                ]
            );
            $baseSalary = $salaryMaster->current_base_salary;
        }

        // 3. 🛡️ PENALTY CALCULATION LOGIC:
        // இந்த ஊழியருக்கு 'Penalty Applied' என்று டேட்டாபேஸில் சேவாகியுள்ள மொத்த அபராதத் தொகையைக் கூட்டுதல்
        $totalPenalties = Attendance::where('emp_id', $employee->id)
            ->where('resolution_status', 'Penalty Applied')
            ->sum('penalty_amount');
        
        return response()->json([
            'success' => true,
            'employee_name' => $employee->name,
            'position' => $employee->position,
            'base_salary' => $baseSalary,
            'penalties' => $totalPenalties // அபராதத் தொகை Pay Report-க்கு அனுப்பப்படுகிறது
        ]);
    }
}