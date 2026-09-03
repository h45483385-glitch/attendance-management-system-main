<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DailyPayLog;

class OvertimeController extends Controller
{
    // பெண்டிங்கில் உள்ள OT ரெக்வெஸ்ட்களை UI-க்கு அனுப்புதல்
    public function index()
    {
        // overtime_status 'pending_approval' ஆக உள்ளதை மட்டும் எடுக்கிறோம்
        $pendingOvertimes = DailyPayLog::where('overtime_status', 'pending_approval')
                                        ->orderBy('created_at', 'desc')
                                        ->get();
                                        
        return view('overtime.index', compact('pendingOvertimes'));
    }

    // Approve பட்டனை அழுத்தினால் நடக்கும் லாஜிக்
    public function approve(Request $request, $id)
    {
        $log = DailyPayLog::findOrFail($id);
        
        $log->overtime_status = 'approved';
        $log->admin_remarks = $request->input('admin_remarks'); // அட்மின் டைப் செய்யும் சேட்/காரணம்
        
        // அப்ரூவ் செய்ததால், OT தொகையை அன்றைய ரெகுலர் சம்பளத்தோடு கூட்டுகிறோம்
        $log->final_day_pay = $log->regular_pay + $log->overtime_pay; 
        $log->save();

        return back()->with('success', 'Overtime Approved Successfully!');
    }

    // Reject பட்டனை அழுத்தினால் நடக்கும் லாஜிக்
    public function reject(Request $request, $id)
    {
        $log = DailyPayLog::findOrFail($id);
        
        $log->overtime_status = 'rejected';
        $log->admin_remarks = $request->input('admin_remarks'); // அட்மின் டைப் செய்யும் சேட்/காரணம்
        
        // ரிஜெக்ட் செய்ததால், OT காசு சேராது. ரெகுலர் சம்பளம் மட்டுமே இருக்கும்.
        $log->final_day_pay = $log->regular_pay; 
        $log->save();

        return back()->with('error', 'Overtime Rejected!');
    }
}