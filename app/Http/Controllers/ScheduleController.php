<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ScheduleController extends Controller
{
    public function index()
    {
        // AUTOMATIC SEEDING: டேட்டாபேஸ் காலியாக இருந்தால் தானாகவே 3 ஷிப்ட்களை உருவாக்கும்
        if (Schedule::count() === 0) {
            Schedule::create([
                'slug' => 'morning-shift',
                'time_in' => '09:00',
                'time_out' => '18:00'
            ]);
            Schedule::create([
                'slug' => 'mid-shift',
                'time_in' => '13:00',
                'time_out' => '22:00'
            ]);
            Schedule::create([
                'slug' => 'night-shift',
                'time_in' => '22:00',
                'time_out' => '07:00'
            ]);
        }

        return view('admin.schedule')
            ->with('schedules', Schedule::all());
    }

    public function store(Request $request)
    {
        $request->validate([
            'slug' => 'required|string',
            'start_time' => 'required',
            'end_time' => 'required',
        ]);

        $schedule = new Schedule();
        $schedule->slug = $request->slug;

        $time_in = Carbon::parse($request->start_time);
        $time_out = Carbon::parse($request->end_time);

        if ($time_out->lessThan($time_in)) {
            $time_out->addDay(); 
        }

        $schedule->time_in = $time_in->format('H:i');
        $schedule->time_out = $time_out->format('H:i');
        
        $schedule->save();

        return redirect()->route('schedule.index');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'slug' => 'required|string',
            'start_time' => 'required',
            'end_time' => 'required',
        ]);

        // Fixed: Use findOrFail to prevent silent record creation on bad IDs
        $schedule = Schedule::findOrFail($id);

        $schedule->slug = $request->slug;

        $time_in = Carbon::parse($request->start_time);
        $time_out = Carbon::parse($request->end_time);

        if ($time_out->lessThan($time_in)) {
            $time_out->addDay(); 
        }

        $schedule->time_in = $time_in->format('H:i');
        $schedule->time_out = $time_out->format('H:i');
        
        $schedule->save();

        return redirect()->route('schedule.index');
    }

    public function destroy($id)
    {
        $schedule = Schedule::findOrFail($id);
        $schedule->delete();

        return redirect()->route('schedule.index');
    }
}