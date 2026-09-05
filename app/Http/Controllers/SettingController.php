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
            $validated = $request->validate([
                'shift_start' => 'required|date_format:H:i',
                'shift_end' => 'required|date_format:H:i',
                'grace_period' => 'required|integer|min:0|max:120',
                'break_duration' => 'required|integer|min:0|max:180'
            ]);

            $setting->shift_start = $validated['shift_start'];
            $setting->shift_end = $validated['shift_end'];
            $setting->grace_period = $validated['grace_period'];
            $setting->break_duration = $validated['break_duration'];
        }
        // 2. Leave ஃபார்ம் டேட்டா வந்தால்...
        elseif($request->has('casual_leaves')) {
            $validated = $request->validate([
                'casual_leaves' => 'required|integer|min:0|max:100',
                'medical_leaves' => 'required|integer|min:0|max:100',
                'min_full_day_hours' => 'required|integer|min:1|max:24'
            ]);

            $setting->casual_leaves = $validated['casual_leaves'];
            $setting->medical_leaves = $validated['medical_leaves'];
            $setting->min_full_day_hours = $validated['min_full_day_hours'];
        }
        // 3. Weekend ஃபார்ம் டேட்டா வந்தால்...
        elseif($request->has('update_weekend')) {
            $setting->is_saturday_off = $request->has('is_saturday_off') ? 1 : 0;
            $setting->is_sunday_off = $request->has('is_sunday_off') ? 1 : 0;
        }
        // 4. Company & General Settings...
        elseif($request->has('update_company')) {
            $validated = $request->validate([
                'company_name' => 'required|string|max:191',
                'timezone' => 'required|string|max:100',
                'financial_year_start' => 'required|string|max:10',
                'currency_symbol' => 'required|string|max:10',
                'currency_code' => 'required|string|max:10',
            ]);

            $setting->fill($validated);
        }
        // 5. Notification & Alerts Gateway...
        elseif($request->has('update_notifications')) {
            $validated = $request->validate([
                'alert_email' => 'nullable|email|max:191'
            ]);

            $setting->notify_payroll_email = $request->has('notify_payroll_email') ? 1 : 0;
            $setting->notify_payroll_sms = $request->has('notify_payroll_sms') ? 1 : 0;
            $setting->notify_late_alerts = $request->has('notify_late_alerts') ? 1 : 0;
            $setting->alert_email = $validated['alert_email'] ?? null;
        }

        $setting->save();
        return back()->with('success', 'System Settings Updated Successfully!');
    }
}