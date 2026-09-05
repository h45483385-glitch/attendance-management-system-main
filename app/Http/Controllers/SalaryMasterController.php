<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SalaryMaster;
use Carbon\Carbon;

class SalaryMasterController extends Controller
{
    public function index()
    {
        // 🚀 AUTOMATED ACTIVATION: Promote any scheduled salaries whose effective date has arrived
        SalaryMaster::whereNotNull('scheduled_salary')
            ->whereNotNull('effective_date')
            ->whereDate('effective_date', '<=', Carbon::today())
            ->get()
            ->each(function ($sal) {
                $sal->current_base_salary = $sal->scheduled_salary;
                $sal->scheduled_salary = null;
                $sal->effective_date = null;
                $sal->save();
            });

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
        $request->validate([
            'new_salary' => 'required|numeric|min:1000',
            'effective_timer' => 'required|in:immediate,next_month,custom',
            'custom_date' => 'required_if:effective_timer,custom|nullable|date|after_or_equal:today'
        ], [
            'new_salary.min' => 'Base salary must be at least ₹1,000.',
            'custom_date.required_if' => 'Please select a valid future date for custom timer scheduling.',
            'custom_date.after_or_equal' => 'Custom effective date must be today or a future date.'
        ]);

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