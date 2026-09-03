<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\BreakLog;
use Carbon\Carbon;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $today = Carbon::today()->toDateString();
        
        // --- 1. SET YOUR SHIFT START TIME HERE ---
        $standardStartTime = '09:30:00'; 

        // Get ALL scans for today to calculate our metrics
        $todaysAttendances = Attendance::where('attendance_date', $today)->get();

        // ---------------------------------------------------------
        // METRIC 1: TOTAL PRESENT TODAY
        // ---------------------------------------------------------
        $totalPresent = $todaysAttendances->unique('emp_id')->count();

        // ---------------------------------------------------------
        // METRIC 2: LATE ARRIVALS (Using our Dynamic Math Engine)
        // ---------------------------------------------------------
        $lateArrivalsCount = 0;
        
        // Isolate the absolute first scan of the morning for each employee
        $employeeFirstScans = $todaysAttendances->groupBy(function($item) {
            return (string) $item->emp_id;
        })->map(function ($records) {
            return $records->sortBy('attendance_time')->first();
        });

        foreach ($employeeFirstScans as $firstScan) {
            $timeIn = Carbon::parse($today . ' ' . $firstScan->attendance_time);
            $shiftStart = Carbon::parse($today . ' ' . $standardStartTime);

            if ($timeIn->greaterThan($shiftStart)) {
                $lateArrivalsCount++;
            }
        }

        // ---------------------------------------------------------
        // METRIC 3: CURRENTLY ON BREAK
        // ---------------------------------------------------------
        // Count how many break_logs started today but have no end time
        $currentlyOnBreak = BreakLog::whereNull('break_end')
            ->whereDate('break_start', Carbon::today())
            ->count();

        // ---------------------------------------------------------
        // METRIC 4: AVERAGE ON-TIME PERCENTAGE
        // ---------------------------------------------------------
        $onTimeCount = $totalPresent - $lateArrivalsCount;
        $avgOnTime = $totalPresent > 0 ? round(($onTimeCount / $totalPresent) * 100) : 100;

        // Pass all the calculated variables directly to your dashboard view!
        // NOTE: If your dashboard view is named something else (like 'admin.dashboard'), update 'home' below.
        return view('home', compact(
            'totalPresent', 
            'lateArrivalsCount', 
            'currentlyOnBreak', 
            'avgOnTime'
        ));
    }
}