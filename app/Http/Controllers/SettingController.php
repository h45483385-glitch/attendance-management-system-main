<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;

class SettingController extends Controller
{
    // செட்டிங்ஸ் பேஜைக் காட்டுவது
    public function index() 
    {
        // டேபிளில் டேட்டா இல்லையென்றால் ID 1-ல் புது டேட்டாவை உருவாக்கும்
        $setting = Setting::firstOrCreate(['id' => 1]);
        return view('admin.settings', compact('setting'));
    }

    // செட்டிங்ஸை அப்டேட் செய்வது
    public function update(Request $request) 
    {
        $setting = Setting::firstOrCreate(['id' => 1]);
        
        // 1. Shift ஃபார்ம் டேட்டா வந்தால்...
        if($request->has('shift_start')) {
            $setting->shift_start = $request->shift_start;
            $setting->shift_end = $request->shift_end;
            $setting->grace_period = $request->grace_period;
        }
        // 2. Leave ஃபார்ம் டேட்டா வந்தால்...
        elseif($request->has('casual_leaves')) {
            $setting->casual_leaves = $request->casual_leaves;
            $setting->medical_leaves = $request->medical_leaves;
            $setting->min_full_day_hours = $request->min_full_day_hours;
        }
        // 3. Weekend ஃபார்ம் டேட்டா வந்தால்...
        elseif($request->has('update_weekend')) {
            $setting->is_saturday_off = $request->has('is_saturday_off') ? 1 : 0;
            $setting->is_sunday_off = $request->has('is_sunday_off') ? 1 : 0;
        }

        $setting->save();
        return back()->with('success', 'System Settings Updated Successfully!');
    }
}