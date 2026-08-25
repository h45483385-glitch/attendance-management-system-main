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
    protected function authenticated(Request $request, $user)
    {
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