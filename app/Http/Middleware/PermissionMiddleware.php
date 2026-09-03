<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $permission
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $permission)
    {
        if (!auth()->check()) {
            return $request->expectsJson() 
                ? response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401)
                : redirect()->guest(route('login'));
        }

        $user = auth()->user();

        // 1. Verify if user is active
        if ($user->status !== 'Active') {
            auth()->logout();
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => 'Account is deactivated.'], 403)
                : redirect()->route('login')->withErrors(['email' => 'Your account has been deactivated.']);
        }

        // 2. Verify account lockout
        if ($user->locked_until && now()->lt($user->locked_until)) {
            auth()->logout();
            $minutes = now()->diffInMinutes($user->locked_until);
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => "Account locked. Try again in {$minutes} minutes."], 403)
                : redirect()->route('login')->withErrors(['email' => "Your account is locked due to repeated failed login attempts. Try again in {$minutes} minutes."]);
        }

        // 3. Verify specific permission
        if (!$user->hasPermission($permission)) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
            }
            abort(403, 'Unauthorized action. You do not have the required permission: ' . $permission);
        }

        return $next($request);
    }
}
