<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Request;

class FallbackAuthenticationService
{
    /**
     * Attempt fallback authentication using Employee ID / Email and a secure PIN or system password.
     */
    public function attempt($identifier, $password, $usePin = false)
    {
        // Rate limiting key based on identifier and IP
        $key = 'fallback_auth:' . $identifier . '|' . Request::ip();
        
        // Lockout after 5 failed attempts
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            $this->logFailure($identifier, "Account locked due to too many attempts. Try again in {$seconds} seconds.");
            return [
                'success' => false, 
                'message' => "Too many attempts. Account locked. Please try again in {$seconds} seconds.",
                'locked' => true
            ];
        }

        // Try to find the employee by email or id
        $employee = Employee::where('email', $identifier)->orWhere('id', $identifier)->first();

        if (!$employee) {
            RateLimiter::hit($key, 300); // 5 min lockout
            $this->logFailure($identifier, 'Invalid credentials (User not found).');
            return ['success' => false, 'message' => 'Invalid credentials.', 'locked' => false];
        }

        // Verify password or PIN
        $authenticated = false;
        
        if ($usePin) {
            // Compare PIN (assuming it is hashed, or plain if prototype)
            // Ideally, this should be Hash::check
            $authenticated = Hash::check($password, $employee->pin_code) || $password === $employee->pin_code;
        } else {
            // Find corresponding system User for password check
            $user = User::where('email', $employee->email)->first();
            if ($user && Hash::check($password, $user->password)) {
                $authenticated = true;
            }
        }

        if ($authenticated) {
            RateLimiter::clear($key);
            $this->logSuccess($employee);
            return [
                'success' => true, 
                'employee' => $employee
            ];
        }

        RateLimiter::hit($key, 300);
        $this->logFailure($identifier, 'Invalid password or PIN.');
        return ['success' => false, 'message' => 'Invalid credentials.', 'locked' => false];
    }

    /**
     * Audit log for successful fallback auth.
     */
    protected function logSuccess($employee)
    {
        AuditLogger::log(
            'FALLBACK_AUTH_SUCCESS',
            'Authentication',
            "Employee {$employee->name} (ID: {$employee->id}) authenticated via Fallback Method.",
            Employee::class,
            $employee->id,
            null,
            ['ip' => Request::ip(), 'user_agent' => Request::userAgent()]
        );
    }

    /**
     * Audit log for failed fallback auth.
     */
    protected function logFailure($identifier, $reason)
    {
        AuditLogger::log(
            'FALLBACK_AUTH_FAILED',
            'Authentication',
            "Failed fallback auth attempt for identifier: {$identifier}. Reason: {$reason}",
            null,
            null,
            null,
            ['ip' => Request::ip(), 'user_agent' => Request::userAgent()]
        );
    }
}
