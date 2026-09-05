<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Holiday;
use App\Models\Setting;
use Carbon\Carbon;

class HolidayController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $year = $request->input('year', Carbon::now()->year);
        $month = $request->input('month', Carbon::now()->month);

        $holidays = Holiday::whereYear('date', $year)
            ->orderBy('date', 'asc')
            ->get();

        $setting = Setting::first();

        // Count holidays by type
        $publicCount = $holidays->where('type', 'public')->count();
        $companyCount = $holidays->where('type', 'company')->count();
        $optionalCount = $holidays->where('type', 'optional')->count();

        return view('settings.holidays', compact('holidays', 'year', 'month', 'setting', 'publicCount', 'companyCount', 'optionalCount'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date|unique:holidays,date',
            'title' => 'required|string|max:191',
            'type' => 'required|in:public,company,optional'
        ], [
            'date.unique' => 'A holiday entry already exists for this date.'
        ]);

        Holiday::create($validated);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Holiday scheduled successfully!']);
        }

        return redirect()->route('holidays.index')->with('success', 'Holiday registered successfully!');
    }

    public function update(Request $request, $id)
    {
        $holiday = Holiday::findOrFail($id);

        $validated = $request->validate([
            'date' => 'required|date|unique:holidays,date,' . $holiday->id,
            'title' => 'required|string|max:191',
            'type' => 'required|in:public,company,optional'
        ]);

        $holiday->update($validated);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Holiday updated successfully!']);
        }

        return redirect()->route('holidays.index')->with('success', 'Holiday updated successfully!');
    }

    public function destroy(Request $request, $id)
    {
        $holiday = Holiday::findOrFail($id);
        $holiday->delete();

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Holiday removed successfully!']);
        }

        return redirect()->route('holidays.index')->with('success', 'Holiday removed successfully!');
    }

    public function events()
    {
        $holidays = Holiday::all()->map(function ($holiday) {
            $color = '#ef4444'; // Public (Red)
            if ($holiday->type === 'company') {
                $color = '#3b82f6'; // Company (Blue)
            } elseif ($holiday->type === 'optional') {
                $color = '#f59e0b'; // Optional (Amber)
            }

            return [
                'id' => $holiday->id,
                'title' => $holiday->title,
                'start' => $holiday->date,
                'type' => $holiday->type,
                'backgroundColor' => $color,
                'borderColor' => $color
            ];
        });

        return response()->json($holidays);
    }
}
