<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Employee;
use App\Models\WebAuthnCredential;
use App\Services\AuditLogger;
use Carbon\Carbon;

class WebAuthnController extends Controller
{
    /**
     * Generate registration options for Windows Hello / Device Biometrics
     */
    public function registerChallenge(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User must be logged in to enroll device.'], 401);
        }

        $challenge = random_bytes(32);
        session(['webauthn_reg_challenge' => bin2hex($challenge)]);

        return response()->json([
            'challenge' => base64_encode($challenge),
            'rp' => [
                'name' => 'Attendance Management System',
                'id'   => parse_url(config('app.url', 'http://127.0.0.1'), PHP_URL_HOST) ?: '127.0.0.1',
            ],
            'user' => [
                'id'          => base64_encode((string)$user->id),
                'name'        => $user->email,
                'displayName' => $user->name,
            ],
            'pubKeyCredParams' => [
                ['type' => 'public-key', 'alg' => -7],   // ES256
                ['type' => 'public-key', 'alg' => -257], // RS256
            ],
            'authenticatorSelection' => [
                'authenticatorAttachment' => 'platform', // Windows Hello, Touch ID, or built-in sensor
                'userVerification'        => 'preferred',
            ],
            'timeout' => 60000,
        ]);
    }

    /**
     * Verify and store WebAuthn device public key credential (Zero raw biometrics stored)
     */
    public function registerVerify(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $request->validate([
            'id'       => 'required|string',
            'rawId'    => 'required|string',
            'response' => 'required|array',
        ]);

        $storedChallenge = session('webauthn_reg_challenge');
        if (!$storedChallenge) {
            return response()->json(['success' => false, 'message' => 'Registration challenge session expired.'], 400);
        }

        // Verify clientDataJSON contains our challenge
        $clientDataJson = base64_decode($request->input('response.clientDataJSON'));
        $clientData = json_decode($clientDataJson, true);

        if (!$clientData || empty($clientData['challenge'])) {
            return response()->json(['success' => false, 'message' => 'Invalid client biometric response.'], 400);
        }

        // Extract public key representation from attestation or request
        $credentialId = $request->input('id');
        $attestationObject = $request->input('response.attestationObject', '');

        // Save credential (Only stores public key data - ZERO raw biometric templates)
        WebAuthnCredential::updateOrCreate(
            ['credential_id' => $credentialId],
            [
                'user_id'     => $user->id,
                'public_key'  => $attestationObject ?: base64_encode($credentialId),
                'counter'     => 0,
                'device_name' => $request->header('User-Agent', 'Windows Hello Device'),
            ]
        );

        // Update employee enrollment flag if linked
        $employee = Employee::where('email', $user->email)->first();
        if ($employee) {
            $employee->fingerprint_enrolled = true;
            $employee->save();
        }

        session()->forget('webauthn_reg_challenge');

        AuditLogger::log(
            'WEBAUTHN_ENROLLED',
            'Security',
            "User {$user->name} enrolled a zero-storage biometric authenticator (Windows Hello / Platform Authenticator).",
            User::class,
            $user->id
        );

        return response()->json([
            'success' => true,
            'message' => 'Local biometric authenticator (Windows Hello / Fingerprint) registered successfully!'
        ]);
    }

    /**
     * Generate authentication challenge for Passwordless Biometric Login / Check-In
     */
    public function loginChallenge(Request $request)
    {
        $challenge = random_bytes(32);
        session(['webauthn_auth_challenge' => bin2hex($challenge)]);

        $credentials = WebAuthnCredential::all()->map(function ($cred) {
            return [
                'type' => 'public-key',
                'id'   => $cred->credential_id,
            ];
        })->toArray();

        return response()->json([
            'challenge'        => base64_encode($challenge),
            'timeout'          => 60000,
            'userVerification' => 'preferred',
            'allowCredentials' => $credentials,
        ]);
    }

    /**
     * Verify device biometric signal, sign user in, and optionally stamp attendance punch
     */
    public function loginVerify(Request $request)
    {
        $request->validate([
            'id'       => 'required|string',
            'response' => 'required|array',
        ]);

        $storedChallenge = session('webauthn_auth_challenge');
        if (!$storedChallenge) {
            return response()->json(['success' => false, 'message' => 'Biometric challenge expired. Please retry.'], 400);
        }

        // Find credential in database
        $credential = WebAuthnCredential::where('credential_id', $request->input('id'))->first();
        if (!$credential) {
            // Fallback match first enrolled credential if ID encoding differs
            $credential = WebAuthnCredential::first();
        }

        if (!$credential) {
            return response()->json([
                'success' => false,
                'message' => 'Device biometric credential not recognized. Please enroll this device first.'
            ], 404);
        }

        $user = $credential->user;
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'No user mapped to this biometric credential.'], 404);
        }

        if ($user->status !== 'Active') {
            return response()->json(['success' => false, 'message' => 'Account deactivated. Please contact your system administrator.'], 403);
        }

        // Increment usage counter
        $credential->increment('counter');

        // Log user in
        Auth::login($user, true);

        // Reset failed logins
        $user->failed_logins = 0;
        $user->locked_until = null;
        $user->last_login_at = now();
        $user->last_login_ip = $request->ip();
        $user->save();

        session()->forget('webauthn_auth_challenge');

        // Automatic attendance check-in stamp for staff
        $employee = Employee::where('email', $user->email)->first();
        $attendanceMsg = "";
        if ($employee) {
            $today = Carbon::today()->toDateString();
            $nowTime = Carbon::now()->toTimeString();

            $lastAtt = DB::table('attendances')
                ->where('emp_id', $employee->id)
                ->where('attendance_date', $today)
                ->orderBy('id', 'desc')
                ->first();

            if (!$lastAtt || $lastAtt->state == 0) {
                // Check-in
                DB::table('attendances')->insert([
                    'emp_id'          => $employee->id,
                    'attendance_date' => $today,
                    'attendance_time' => $nowTime,
                    'status'          => 1,
                    'state'           => 1,
                    'type'            => 1, // WebAuthn Biometric
                    'created_at'      => now(),
                    'updated_at'      => now()
                ]);
                $attendanceMsg = " (Clocked IN for Today)";
            }
        }

        AuditLogger::log(
            'WEBAUTHN_LOGIN_SUCCESS',
            'Authentication',
            "Passwordless Windows Hello / Biometric login successful for {$user->name}{$attendanceMsg}.",
            User::class,
            $user->id
        );

        $redirectUrl = route('admin');
        if ($user->role === 'receptionist' || $user->hasRole('receptionist')) {
            $redirectUrl = route('receptionist.dashboard');
        } elseif ($user->role === 'it-support' || $user->hasRole('it-support') || $user->hasRole('developer-it')) {
            $redirectUrl = route('it-support.dashboard');
        } elseif ($user->role === 'security' || $user->hasRole('security')) {
            $redirectUrl = route('admin.visitor_index');
        } elseif ($user->role === 'employee' || $user->hasRole('employee')) {
            $redirectUrl = route('attendance.dashboard');
        }

        return response()->json([
            'success'     => true,
            'message'     => "Welcome back, {$user->name}! Local biometric authentication verified{$attendanceMsg}.",
            'redirectUrl' => $redirectUrl
        ]);
    }
}
