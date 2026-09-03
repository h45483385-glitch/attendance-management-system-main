<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\AuditLogger;

class SecuritySettingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:security.manage');
    }

    public function index()
    {
        // Fetch or set default security settings
        $keys = [
            'max_failed_attempts' => '5',
            'lockout_duration' => '15',
            'session_timeout' => '120',
            'password_min_length' => '6'
        ];

        $settings = [];
        foreach ($keys as $key => $default) {
            $setting = DB::table('security_settings')->where('key', $key)->first();
            if (!$setting) {
                DB::table('security_settings')->insert([
                    'key' => $key,
                    'value' => $default,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                $settings[$key] = $default;
            } else {
                $settings[$key] = $setting->value;
            }
        }

        return view('admin.security.settings', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'max_failed_attempts' => 'required|integer|min:3|max:20',
            'lockout_duration' => 'required|integer|min:1|max:1440',
            'session_timeout' => 'required|integer|min:5|max:1440',
            'password_min_length' => 'required|integer|min:6|max:32'
        ]);

        DB::beginTransaction();
        try {
            $keys = ['max_failed_attempts', 'lockout_duration', 'session_timeout', 'password_min_length'];
            $beforeData = [];
            $afterData = [];

            foreach ($keys as $key) {
                $current = DB::table('security_settings')->where('key', $key)->value('value');
                $beforeData[$key] = $current;
                
                $newValue = $request->input($key);
                $afterData[$key] = $newValue;

                DB::table('security_settings')->where('key', $key)->update([
                    'value' => $newValue,
                    'updated_at' => now()
                ]);
            }

            AuditLogger::log(
                'SECURITY_SETTING_CHANGED',
                'Security',
                "Updated global security policy settings",
                null,
                null,
                $beforeData,
                $afterData
            );

            DB::commit();
            flash()->success('Success', 'Global security policies updated successfully!');
            return redirect()->route('security.settings');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to save security settings: ' . $e->getMessage()]);
        }
    }
}
