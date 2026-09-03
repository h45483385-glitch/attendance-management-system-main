<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Attendance;
use App\Models\Employee;

/*
|--------------------------------------------------------------------------
| API Routes (NO AUTH, PURE JSON)
|--------------------------------------------------------------------------
*/

Route::post('/face-attendance', function (Request $request) {

    try {

        // FORCE JSON RESPONSE
        request()->headers->set('Accept', 'application/json');

        // VALIDATION
        if (!$request->emp_id) {
            return response()->json([
                'status' => false,
                'message' => 'emp_id is required'
            ], 422);
        }

        if (!Employee::where('id', $request->emp_id)->exists()) {
            return response()->json([
                'status' => false,
                'message' => 'Employee not found'
            ], 404);
        }

        // TIME
        $today = now()->toDateString();
        $time = now()->format('H:i:s');

        // CHECK EXISTING RECORD
        $attendance = Attendance::where('emp_id', $request->emp_id)
            ->whereDate('attendance_date', $today)
            ->first();

        // ✅ CHECK-IN
        if (!$attendance) {

            $attendance = Attendance::create([
                'emp_id' => $request->emp_id,
                'attendance_date' => $today,
                'attendance_time' => $time,
                'check_in_time' => $time,
                'check_out_time' => null,
                'state' => 1,
                'status' => 1,
                'type' => 0
            ]);

            return response()->json([
                'status' => true,
                'action' => 'CHECK_IN',
                'message' => 'Check-in successful',
                'data' => $attendance
            ]);
        }

        // ✅ CHECK-OUT
        if (empty($attendance->check_out_time)) {

            $attendance->update([
                'check_out_time' => $time
            ]);

            return response()->json([
                'status' => true,
                'action' => 'CHECK_OUT',
                'message' => 'Check-out successful',
                'data' => $attendance
            ]);
        }

        // ✅ ALREADY DONE
        return response()->json([
            'status' => false,
            'message' => 'Already completed IN & OUT for today'
        ]);

    } catch (\Exception $e) {

        return response()->json([
            'status' => false,
            'message' => 'Server error',
            'error' => $e->getMessage()
        ], 500);
    }
});