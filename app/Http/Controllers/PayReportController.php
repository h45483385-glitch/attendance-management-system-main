<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\SalaryMaster;
use Carbon\Carbon;

class PayReportController extends Controller
{
    public function index()
    {
        // எல்லா எம்ப்ளாயி டேட்டாவையும் UI-க்கு அனுப்புகிறோம்
        $employees = Employee::all();
        
        return view('pay-report.index', compact('employees'));
    }

    // JS-ல் இருந்து fetchPayData(id) என்று அழைக்கும்போது டேட்டாவை அனுப்பும் API
    public function fetchPayData($id)
    {
        $employee = Employee::find($id);
        
        if(!$employee) {
            return response()->json(['success' => false, 'message' => 'Employee not found']);
        }

        // 1. எம்ப்ளாயியின் பதவியை (Position) வைத்து மாஸ்டரில் தேடுகிறோம்
        $salaryMaster = SalaryMaster::where('designation', $employee->position)->first();
        
        // 2. மாஸ்டரில் இருந்தால் அந்தச் சம்பளம், இல்லையென்றால் 0
        $baseSalary = $salaryMaster ? $salaryMaster->current_base_salary : 0;

        // (எதிர்காலத்தில் இங்கே அட்டனன்ஸ் டேபிளில் இருந்து Total Present/Absent எடுக்கும் லாஜிக் வரும்)
        
        return response()->json([
            'success' => true,
            'employee_name' => $employee->name,
            'position' => $employee->position,
            'base_salary' => $baseSalary
        ]);
    }
}