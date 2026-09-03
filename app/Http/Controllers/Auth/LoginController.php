<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request; // 🚀 இதை புதிதாக சேர்த்துள்ளோம்

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen.
    |
    */

    use AuthenticatesUsers;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * 🚀 THE FIX: Role-Based Redirection Logic
     * யூசர் வெற்றிகரமாக லாகின் செய்ததும் எங்கு செல்ல வேண்டும் என்பதை இது முடிவு செய்யும்.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function validateLogin(Request $request)
    {
        $request->validate([
            $this->username() => 'required|string',
            'password' => 'required|string',
        ]);

        $user = \App\Models\User::where('email', $request->email)->first();
        if ($user) {
            if ($user->status !== 'Active') {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    $this->username() => ['This account has been deactivated. Please contact your system administrator.'],
                ]);
            }

            if ($user->locked_until && now()->lt($user->locked_until)) {
                $minutes = now()->diffInMinutes($user->locked_until);
                throw \Illuminate\Validation\ValidationException::withMessages([
                    $this->username() => ["This account is temporarily locked due to repeated failed login attempts. Try again in {$minutes} minutes."],
                ]);
            }
        }
    }

    protected function incrementLoginAttempts(Request $request)
    {
        $user = \App\Models\User::where('email', $request->email)->first();
        if ($user) {
            $user->increment('failed_logins');
            
            $maxAttempts = (int) \DB::table('security_settings')->where('key', 'max_failed_attempts')->value('value') ?: 5;
            $lockoutDuration = (int) \DB::table('security_settings')->where('key', 'lockout_duration')->value('value') ?: 15;

            if ($user->failed_logins >= $maxAttempts) {
                $user->locked_until = now()->addMinutes($lockoutDuration);
                $user->save();

                \App\Services\AuditLogger::log('LOGIN_LOCKOUT', 'Authentication', "Account locked: {$user->email} due to {$user->failed_logins} failed attempts.", \App\Models\User::class, $user->id);
            } else {
                \App\Services\AuditLogger::log('LOGIN_FAILED', 'Authentication', "Failed login attempt for user: {$user->email} (Attempts: {$user->failed_logins}/{$maxAttempts})");
            }
        } else {
            \App\Services\AuditLogger::log('LOGIN_FAILED', 'Authentication', "Failed login attempt for unregistered email: {$request->email}");
        }

        $this->fireLockoutEvent($request);
    }

    protected function authenticated(Request $request, $user)
    {
        // Reset failed logins on success
        $user->failed_logins = 0;
        $user->locked_until = null;
        $user->last_login_at = now();
        $user->last_login_ip = $request->ip();
        $user->save();

        // Audit log
        \App\Services\AuditLogger::log('LOGIN', 'Authentication', "User {$user->name} logged in successfully from IP: {$request->ip()}", \App\Models\User::class, $user->id);

        // 1. ADMIN LOGIC (அட்மின் லாகின் செய்தால்)
        if ($user->role === 'admin' || $user->hasRole('admin')) {
            return redirect()->route('admin'); // Main Admin Dashboard
        }
        
        // 2. RECEPTIONIST / SECURITY LOGIC (ரிசப்ஷன் லாகின் செய்தால்)
        elseif ($user->role === 'receptionist' || $user->role === 'security' || $user->hasRole('receptionist')) {
            return redirect()->route('admin.visitor_index'); // Visitor Logs Page
        }

        // 3. EMPLOYEE / STAFF LOGIC (எம்ப்ளாயி லாகின் செய்தால்)
        elseif ($user->role === 'employee' || $user->role === 'staff' || $user->hasRole('employee')) {
            return redirect()->route('attendance.dashboard'); // Personal Dashboard
        }

        // 4. DEFAULT FALLBACK (எதுவுமே மேட்ச் ஆகவில்லை என்றால்)
        return redirect(RouteServiceProvider::HOME);
    }
}