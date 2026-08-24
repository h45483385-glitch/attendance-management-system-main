<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Aws\Rekognition\RekognitionClient;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB; 
use Carbon\Carbon;
use App\Models\Employee; // <-- THE FIX: Added Employee Model to check if user exists

class FaceController extends Controller
{
    public function scanFace(Request $request)
    {
        try {
            // 1. Process Kiosk Image
            $imageData = $request->input('image');
            if (!$imageData) {
                return response()->json(['success' => false, 'message' => 'No camera image received.'], 400);
            }
            $image = str_replace('data:image/jpeg;base64,', '', $imageData);
            $image = str_replace(' ', '+', $image);
            $imageBytes = base64_decode($image);

            // 2. Connect to AWS
            $rekognition = new RekognitionClient([
                'region'    => env('AWS_DEFAULT_REGION', 'ap-south-1'),
                'version'   => 'latest',
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
                    
                    // Create new attendance row
                    DB::table('attendances')->insert([
                        'emp_id' => $employeeId,
                        'attendance_date' => $today,
                        'attendance_time' => $currentTime,
                        'status' => 1, 
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

        } catch (\Exception $e) {
            Log::error('Attendance Check-in Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Server error: ' . $e->getMessage()], 500);
        }
    }
}