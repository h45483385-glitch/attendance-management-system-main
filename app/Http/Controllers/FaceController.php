<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Aws\Rekognition\RekognitionClient;
use Aws\Exception\CredentialsException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Employee;

class FaceController extends Controller
{
    public function scanFace(Request $request)
    {
        // Guard: fail fast with a clear message if AWS credentials are not configured.
        // Without this, the SDK silently falls back to the EC2 metadata endpoint
        // (169.254.169.254) which times out on local environments.
        if (empty(env('AWS_ACCESS_KEY_ID')) || empty(env('AWS_SECRET_ACCESS_KEY'))) {
            Log::warning('AWS credentials are not set in .env — face scan aborted.');
            return response()->json([
                'success' => false,
                'message' => 'Face recognition is not configured. Please contact your system administrator.'
            ], 503);
        }

        try {
            // 1. Process Kiosk Image
            $imageData = $request->input('image');
            if (!$imageData) {
                return response()->json(['success' => false, 'message' => 'No camera image received.'], 400);
            }
            $image = str_replace('data:image/jpeg;base64,', '', $imageData);
            $image = str_replace(' ', '+', $image);
            $imageBytes = base64_decode($image);

            // 2. Connect to AWS — credentials passed explicitly so the SDK never
            //    attempts the EC2 Instance Metadata Service fallback on local machines.
            $rekognition = new RekognitionClient([
                'region'      => env('AWS_DEFAULT_REGION', 'ap-southeast-1'),
                'version'     => 'latest',
                'credentials' => [
                    'key'    => env('AWS_ACCESS_KEY_ID'),
                    'secret' => env('AWS_SECRET_ACCESS_KEY'),
                ],
            ]);

            // 3. Search Faces
            $result = $rekognition->searchFacesByImage([
                'CollectionId' => 'pragnaware-employee-faces', 
                'Image' => ['Bytes' => $imageBytes],
                'MaxFaces' => 1,
                'FaceMatchThreshold' => 90, 
            ]);

            // 4. Handle Logic
            if (!empty($result['FaceMatches'])) {
                
                $employeeId = $result['FaceMatches'][0]['Face']['ExternalImageId'];
                
                // =========================================================
                // 🚀 THE FIX: Check if Employee actually exists in Database!
                // =========================================================
                $employeeExists = Employee::find($employeeId);
                
                if (!$employeeExists) {
                    return response()->json([
                        'success' => false, 
                        'message' => 'Unrecognized Face / Employee ID (' . $employeeId . ') not found in database.'
                    ]);
                }
                
                $today = Carbon::today()->toDateString();
                $currentTime = Carbon::now()->toTimeString();

                // Find last record for today
                $lastRecord = DB::table('attendances')
                    ->where('emp_id', $employeeId)
                    ->where('attendance_date', $today)
                    ->orderBy('id', 'desc')
                    ->first();

                if (!$lastRecord || $lastRecord->state == 0) {
                    // ==========================================
                    // ACTION: CHECK-IN (Returning to work)
                    // ==========================================
                    $newState = 1; 
                    $statusMessage = 'Face Verified! Check-IN successful for: ' . $employeeExists->name;
                    
                    $schedule = $employeeExists->schedules->first();
                    $employeeStartTime = $schedule ? $schedule->time_in : '09:30:00';
                    $employeeEndTime = $schedule ? $schedule->time_out : '18:30:00';
                    
                    $shiftStart = Carbon::parse($today . ' ' . $employeeStartTime);
                    $shiftEnd = Carbon::parse($today . ' ' . $employeeEndTime);
                    $timeIn = Carbon::parse($today . ' ' . $currentTime);
                    
                    if ($timeIn->greaterThan($shiftEnd)) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Punch rejected: Your scheduled shift has already ended (' . date('h:i A', strtotime($employeeEndTime)) . ').'
                        ]);
                    }
                    
                    $isLate = $timeIn->greaterThan($shiftStart) ? 0 : 1;

                    // Create new attendance row
                    DB::table('attendances')->insert([
                        'emp_id' => $employeeId,
                        'attendance_date' => $today,
                        'attendance_time' => $currentTime,
                        'status' => $isLate, 
                        'state' => $newState, 
                        'type' => 0,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);

                    // --- MNC BREAK LOGIC: CLOSING THE BREAK ---
                    if ($lastRecord && $lastRecord->state == 0) {
                        $openBreak = DB::table('break_logs')
                            ->where('attendance_id', $lastRecord->id)
                            ->whereNull('break_end')
                            ->first();

                        if ($openBreak) {
                            $breakStart = Carbon::parse($openBreak->break_start);
                            $breakEnd = Carbon::now();
                            $durationMinutes = $breakStart->diffInMinutes($breakEnd);

                            // MNC Rule: Only count as a break if it's <= 120 mins
                            if ($durationMinutes <= 120) {
                                DB::table('break_logs')
                                    ->where('id', $openBreak->id)
                                    ->update([
                                        'break_end' => $breakEnd->toDateTimeString(),
                                        'updated_at' => now()
                                    ]);
                            } else {
                                DB::table('break_logs')->where('id', $openBreak->id)->delete();
                            }
                        }
                    }

                } else {
                    // ==========================================
                    // ACTION: CHECK-OUT (Leaving for break or home)
                    // ==========================================
                    $newState = 0; 
                    $statusMessage = 'Face Verified! Check-OUT successful for: ' . $employeeExists->name;
                    
                    // Stamp the checkout time
                    DB::table('attendances')
                        ->where('id', $lastRecord->id)
                        ->update([
                            'check_out_time' => $currentTime,
                            'state' => $newState,
                            'updated_at' => now()
                        ]);

                    // --- MNC BREAK LOGIC: STARTING THE BREAK ---
                    $currentHour = Carbon::now()->hour;
                    if ($currentHour < 16) { 
                        DB::table('break_logs')->insert([
                            'attendance_id' => $lastRecord->id,
                            'emp_id' => $employeeId,
                            'break_start' => Carbon::now()->toDateTimeString(),
                            'break_end' => null,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    }
                }
                
                return response()->json([
                    'success' => true,
                    'message' => $statusMessage,
                    'state' => $newState 
                ]);

            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No face detected or recognized. Please ensure you are well-lit and looking at the camera.'
                ]);
            }

        } catch (CredentialsException $e) {
            // Specific catch for AWS credential failures (blank keys, metadata timeout, etc.)
            Log::error('AWS Credentials Error in scanFace: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'AWS credentials are invalid or missing. Please check your .env configuration.'
            ], 503);
        } catch (\Exception $e) {
            Log::error('Attendance Check-in Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Server error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Fallback Check-In mechanism for employees lacking biometric hardware or failing scans.
     */
    public function fallbackCheckIn(Request $request, \App\Services\FallbackAuthenticationService $authService)
    {
        $request->validate([
            'identifier' => 'required|string',
            'password' => 'required|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'address' => 'nullable|string'
        ]);

        $result = $authService->attempt($request->identifier, $request->password, true); // true = use PIN for kiosk

        if (!$result['success']) {
            return response()->json($result, 403);
        }

        $employeeExists = $result['employee'];
        $today = Carbon::today()->toDateString();
        $currentTime = Carbon::now()->toTimeString();

        // Find last record for today
        $lastRecord = DB::table('attendances')
            ->where('emp_id', $employeeExists->id)
            ->where('attendance_date', $today)
            ->orderBy('id', 'desc')
            ->first();

        // Exact same logic as face scan
        if (!$lastRecord || $lastRecord->state == 0) {
            // Check-IN
            $newState = 1; 
            $statusMessage = 'Credential Verified! Check-IN successful for: ' . $employeeExists->name;
            
            $schedule = $employeeExists->schedules->first();
            $employeeStartTime = $schedule ? $schedule->time_in : '09:30:00';
            $employeeEndTime = $schedule ? $schedule->time_out : '18:30:00';
            
            $shiftStart = Carbon::parse($today . ' ' . $employeeStartTime);
            $shiftEnd = Carbon::parse($today . ' ' . $employeeEndTime);
            $timeIn = Carbon::parse($today . ' ' . $currentTime);
            
            if ($timeIn->greaterThan($shiftEnd)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Punch rejected: Your scheduled shift has already ended.'
                ]);
            }
            
            $isLate = $timeIn->greaterThan($shiftStart) ? 0 : 1;

            DB::table('attendances')->insert([
                'emp_id' => $employeeExists->id,
                'attendance_date' => $today,
                'attendance_time' => $currentTime,
                'status' => $isLate, 
                'state' => $newState, 
                'type' => 0,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        } else {
            // Check-OUT
            $newState = 0; 
            $statusMessage = 'Credential Verified! Check-OUT successful for: ' . $employeeExists->name;
            
            DB::table('attendances')
                ->where('id', $lastRecord->id)
                ->update([
                    'check_out_time' => $currentTime,
                    'state' => $newState,
                    'updated_at' => now()
                ]);
        }
        
        return response()->json([
            'success' => true,
            'message' => $statusMessage,
            'state' => $newState 
        ]);
    }
}