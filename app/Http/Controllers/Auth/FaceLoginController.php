<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Aws\Rekognition\RekognitionClient;
use Aws\Exception\CredentialsException;
use App\Models\Employee;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\FaceMismatchAlertService;

class FaceLoginController extends Controller
{
    /**
     * Authenticate a user via Passwordless Facial Recognition
     */
    public function loginWithFace(Request $request)
    {
        $request->validate([
            'image' => 'required|string',
        ]);

        // Guard: check AWS credentials
        if (empty(env('AWS_ACCESS_KEY_ID')) || empty(env('AWS_SECRET_ACCESS_KEY'))) {
            return response()->json([
                'success' => false,
                'message' => 'Facial recognition service is not configured on this server.'
            ], 503);
        }

        try {
            // 1. Decode base64 image
            $imageData = $request->input('image');
            if (strpos($imageData, 'data:image') !== false) {
                $imageData = explode(',', $imageData)[1];
            }
            $imageData = str_replace(' ', '+', $imageData);
            $imageBytes = base64_decode($imageData);

            if (!$imageBytes) {
                return response()->json(['success' => false, 'message' => 'Invalid image data captured.'], 400);
            }

            // 2. Query AWS Rekognition with QualityFilter => 'LOW' for low-res webcam support
            $rekognition = new RekognitionClient([
                'region'      => env('AWS_DEFAULT_REGION', 'ap-south-1'),
                'version'     => 'latest',
                'credentials' => [
                    'key'    => env('AWS_ACCESS_KEY_ID'),
                    'secret' => env('AWS_SECRET_ACCESS_KEY'),
                ],
            ]);

            $result = $rekognition->searchFacesByImage([
                'CollectionId'       => 'pragnaware-employee-faces',
                'Image'              => ['Bytes' => $imageBytes],
                'MaxFaces'           => 1,
                'FaceMatchThreshold' => 85, // Balanced threshold for high accuracy + low-res tolerance
                'QualityFilter'      => 'LOW',
            ]);

            // 3. Evaluate Match
            if (!empty($result['FaceMatches'])) {
                $matchedFace = $result['FaceMatches'][0]['Face'];
                $employeeId = $matchedFace['ExternalImageId'];
                $confidence = round($result['FaceMatches'][0]['Similarity'], 1);

                $employee = Employee::find($employeeId);
                if (!$employee) {
                    FaceMismatchAlertService::triggerMismatchAlert("Unregistered Employee ID #{$employeeId} matched with {$confidence}% similarity.", $request->ip());
                    return response()->json([
                        'success' => false,
                        'message' => "Face matched with {$confidence}% similarity, but no active employee profile exists."
                    ], 404);
                }

                // Locate user account
                $user = User::where('email', $employee->email)->first();
                if (!$user) {
                    // Fallback to employee user mapping by name or pin
                    $user = User::where('name', $employee->name)->first();
                }

                if (!$user) {
                    return response()->json([
                        'success' => false,
                        'message' => "Employee profile ({$employee->name}) verified, but no web login account is linked to this email."
                    ], 404);
                }

                if ($user->status !== 'Active') {
                    return response()->json([
                        'success' => false,
                        'message' => 'This account has been deactivated. Please contact your system administrator.'
                    ], 403);
                }

                // Log in the user
                Auth::login($user, true);

                // Reset failed attempts & update audit info
                $user->failed_logins = 0;
                $user->locked_until = null;
                $user->last_login_at = now();
                $user->last_login_ip = $request->ip();
                $user->save();

                AuditLogger::log('FACE_LOGIN_SUCCESS', 'Authentication', "Passwordless Face ID login successful for {$user->name} ({$user->email}) with {$confidence}% confidence.", User::class, $user->id);

                // Determine redirect path by role
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
                    'message'     => "Welcome, {$user->name}! Face verified ({$confidence}%).",
                    'redirectUrl' => $redirectUrl,
                ]);

            } else {
                // Mismatch or unindexed face
                FaceMismatchAlertService::triggerMismatchAlert("Face recognition failed to find a matching employee profile.", $request->ip());

                return response()->json([
                    'success' => false,
                    'message' => 'Face not recognized. Please ensure your face is well-lit and directly facing the camera.'
                ], 401);
            }

        } catch (CredentialsException $e) {
            Log::error('AWS Rekognition Credentials Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'AWS credentials error. Please verify AWS configuration.'
            ], 503);
        } catch (\Exception $e) {
            Log::error('Face Login Exception: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Authentication error: ' . $e->getMessage()
            ], 500);
        }
    }
}
