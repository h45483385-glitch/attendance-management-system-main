<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Employee;
use App\Models\Role;
use App\Models\Schedule;
use App\Models\SalaryMaster; 
use App\Models\Attendance;
use App\Http\Requests\EmployeeRec;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Aws\Rekognition\RekognitionClient;
use Illuminate\Support\Facades\Log;

class EmployeeController extends Controller
{
    public function index()
    {
        return view('admin.employee')->with([
            'employees' => Employee::all(),
            'schedules' => Schedule::all()
        ]);
    }

    public function store(Request $request)
    {
        // 1. Validate Input
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'pin_code' => 'required|min:4',
            'schedule' => 'required'
        ]);

        // 2. CREATE LOGIN ACCOUNT IN 'users' TABLE
        $user = new User;
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = bcrypt($request->pin_code);
        
        $position = strtolower($request->position ?? '');
        if (str_contains($position, 'admin')) {
            $user->role = 'admin';
        } elseif (str_contains($position, 'reception') || str_contains($position, 'security')) {
            $user->role = 'receptionist';
        } else {
            $user->role = 'employee';
        }
        $user->save();

        // 3. CREATE PROFILE IN 'employees' TABLE
        $employee = new Employee;
        $employee->name = $request->name;
        $employee->position = $request->position ?? 'Staff';
        $employee->email = $request->email;
        $employee->pin_code = bcrypt($request->pin_code);
        $employee->save();

        // ======================================================
        // PIPELINE 1: AUTO-SYNC WITH SALARY MASTER
        // ======================================================
        if ($request->position) {
            SalaryMaster::firstOrCreate(
                ['designation' => $request->position],
                [
                    'department' => 'General', 
                    'current_base_salary' => 0, 
                ]
            );
        }

        // 4. LINK SCHEDULE / SHIFT
        if ($request->schedule) {
            $schedule = Schedule::whereSlug($request->schedule)->first();
            if($schedule){
                $employee->schedules()->attach($schedule);
            }
        }

        flash()->success('Success', 'Employee Account, Shift Assignment & Login Access Created Successfully!');

        return redirect()->route('employees.index')->with('success');
    }

    public function update(EmployeeRec $request, Employee $employee)
    {
        $request->validated();

        $employee->name = $request->name;
        $employee->position = $request->position;
        $employee->email = $request->email;
        $employee->pin_code = bcrypt($request->pin_code);
        $employee->save();

        // ======================================================
        // PIPELINE 1: AUTO-SYNC WITH SALARY MASTER ON UPDATE
        // ======================================================
        if ($request->position) {
            SalaryMaster::firstOrCreate(
                ['designation' => $request->position],
                [
                    'department' => 'General',
                    'current_base_salary' => 0,
                ]
            );
        }

        if ($request->schedule) {
            $employee->schedules()->detach();

            $schedule = Schedule::whereSlug($request->schedule)->first();
            if($schedule){
                $employee->schedules()->attach($schedule);
            }
        }

        flash()->success('Success', 'Employee Record has been Updated successfully !');

        return redirect()->route('employees.index')->with('success');
    }

    // ======================================================
    // 🚀 CASCADE DELETE PIPELINE (Deletes User, Attendance & Profile)
    // ======================================================
    public function destroy(Employee $employee)
    {
        // 1. Delete associated login account from 'users' table using email
        User::where('email', $employee->email)->delete();

        // 2. Delete all attendance records associated with this employee
        Attendance::where('emp_id', $employee->id)->delete();

        // 3. Detach schedule relationships
        $employee->schedules()->detach();

        // 4. Finally delete the employee profile record
        $employee->delete();

        flash()->success('Success', 'Employee Account, Attendance Logs & Profile Deleted Successfully!');

        return redirect()->route('employees.index')->with('success');
    }

   
    // ======================================================
    // 📸 FACE CAPTURE MODULE (AWS S3 + REKOGNITION INDEXING)
    // ======================================================
    public function captureFace(Request $request, Employee $employee)
    {
        try {
            // -------------------------
            // CHECK & FORMAT IMAGE
            // -------------------------
            if (!$request->has('image')) {
                return response()->json(['status' => false, 'message' => 'No image received.']);
            }

            $image = $request->image;
            if (strpos($image, 'data:image') !== false) {
                $image = explode(',', $image)[1];
            }
            $image = str_replace(' ', '+', $image);
            $imageData = base64_decode($image);

            if ($imageData === false) {
                return response()->json(['status' => false, 'message' => 'Invalid image data.']);
            }

            // -------------------------
            // 1. SAVE TO AWS S3 (Backup)
            // -------------------------
            $safeName = Str::slug($employee->name); 
            $fileName = $safeName . '_' . $employee->id . '_' . time() . '.jpeg';

            try {
                Storage::disk('s3')->put('faces/' . $fileName, $imageData);
            } catch (\Exception $s3Exception) {
                Log::error('S3 Upload Failed: ' . $s3Exception->getMessage());
            }

            // -------------------------
            // 2. INDEX FACE IN AWS REKOGNITION 
            // -------------------------
            try {
                $rekognition = new RekognitionClient([
                    'region'    => env('AWS_DEFAULT_REGION', 'ap-south-1'),
                    'version'   => 'latest',
                ]);

                $result = $rekognition->indexFaces([
                    'CollectionId' => 'pragnaware-employee-faces',
                    'Image' => [
                        'Bytes' => $imageData
                    ],
                    'ExternalImageId' => (string) $employee->id,
                    'DetectionAttributes' => ['DEFAULT']
                ]);

                if (empty($result['FaceRecords'])) {
                    return response()->json([
                        'status' => false,
                        'message' => 'No Face Detected! Please ensure your face is clearly visible and try again in a well-lit area.'
                    ]);
                }

            } catch (\Exception $rekognitionException) {
                Log::error('AWS Rekognition Indexing Failed: ' . $rekognitionException->getMessage());
                return response()->json([
                    'status' => false,
                    'message' => 'AWS Error: Failed to register face. ' . $rekognitionException->getMessage()
                ]);
            }

            // -------------------------
            // 3. DATABASE INSERT TRIGGER 
            // -------------------------
            $attendance = new Attendance();
            $attendance->emp_id = $employee->id;
            $attendance->attendance_date = now()->toDateString();
            $attendance->attendance_time = now()->toTimeString();
            $attendance->state = 0;
            $attendance->status = 1;
            $attendance->type = 0;
            $attendance->save();

            // -------------------------
            // RESPONSE
            // -------------------------
            return response()->json([
                'status' => true,
                'message' => 'Face securely registered and indexed in AWS Rekognition!',
                'file' => $fileName,
                'path' => 'faces/' . $fileName
            ]);

        } catch (\Exception $e) {
            Log::error('Face Registration Master Error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Server Error: ' . $e->getMessage()
            ]);
        }
    }
}