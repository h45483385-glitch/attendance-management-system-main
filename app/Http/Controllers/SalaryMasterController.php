<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SalaryMaster;
use Carbon\Carbon;

class SalaryMasterController extends Controller
{
    public function index()
    {
        // UI-ல் காட்டுவதற்காக முதல் முறையாக டம்மி டிபார்ட்மென்ட் டேட்டாவை உருவாக்குதல்
        if(SalaryMaster::count() == 0) {
            SalaryMaster::insert([
                ['department' => 'Engineering', 'designation' => 'Intern', 'current_base_salary' => 15000],
                ['department' => 'Engineering', 'designation' => 'Junior Developer', 'current_base_salary' => 30000],
                ['department' => 'Engineering', 'designation' => 'Cloud Engineer', 'current_base_salary' => 45000],
            ]);
        }
        
        $salaries = SalaryMaster::orderBy('department')->get();
        return view('settings.salary-master', compact('salaries'));
    }

    public function update(Request $request, $id)
    {
        $salary = SalaryMaster::findOrFail($id);
        $newSalary = $request->input('new_salary');
        $timerOption = $request->input('effective_timer');
        
        $effectiveDate = null;
        
        if($timerOption == 'immediate') {
            $effectiveDate = Carbon::now()->toDateString();
        } elseif($timerOption == 'next_month') {
            $effectiveDate = Carbon::now()->addMonth()->startOfMonth()->toDateString(); 
        } else {
            $effectiveDate = $request->input('custom_date');
        }

        if($timerOption == 'immediate') {
            $salary->current_base_salary = $newSalary;
            $salary->scheduled_salary = null;
            $salary->effective_date = null;
        } else {
            $salary->scheduled_salary = $newSalary;
            $salary->effective_date = $effectiveDate;
        }

        $salary->save();

        return back()->with('success', 'Salary structure updated successfully with Timer!');
    }
}