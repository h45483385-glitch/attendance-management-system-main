<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Employee;
use App\Models\Schedule;
use App\Models\SalaryMaster; 
use App\Models\Attendance;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Aws\Rekognition\RekognitionClient;
use Illuminate\Support\Facades\Log;

class EmployeeController extends Controller
{
    public function index()
    {
        return view('admin.employee')->with([
            'employees' => Employee::with('shift')->get(),
            'schedules' => Schedule::all()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'pin_code' => 'required|min:4',
            'schedule_id' => 'required'
        ]);

        // 1. Create Login Account
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

        // 2. Create Employee Profile with Direct Department & Schedule ID
        $employee = new Employee;
        $employee->name = $request->name;
        $employee->department = $request->department ?? 'General';
        $employee->position = $request->position ?? 'Staff';
        $employee->email = $request->email;
        $employee->pin_code = bcrypt($request->pin_code);
        $employee->schedule_id = $request->schedule_id;
        $employee->save();

        // 3. Auto-sync with Salary Master
        if ($request->position) {
            SalaryMaster::firstOrCreate(
                ['designation' => $request->position],
                [
                    'department' => $request->department ?? 'General', 
                    'current_base_salary' => 0, 
                ]
            );
        }

        flash()->success('Success', 'Employee Account, Department & Shift Created Successfully!');
        return redirect()->route('employees.index')->with('success');
    }

    public function update(Request $request, Employee $employee)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $employee->id,
            'schedule_id' => 'required'
        ]);

        // Direct Update
        $employee->name = $request->name;
        $employee->department = $request->department ?? 'General';
        $employee->position = $request->position ?? 'Staff';
        $employee->email = $request->email;
        $employee->schedule_id = $request->schedule_id;
        
        if ($request->filled('pin_code')) {
            $employee->pin_code = bcrypt($request->pin_code);
        }
        $employee->save();

        // Update corresponding User table
        User::where('email', $employee->getOriginal('email'))->update([
            'name' => $request->name,
            'email' => $request->email,
            ...($request->filled('pin_code') ? ['password' => bcrypt($request->pin_code)] : [])
        ]);

        flash()->success('Success', 'Employee Record, Department & Shift Updated Successfully!');
        return redirect()->route('employees.index')->with('success');
    }

    public function destroy(Employee $employee)
    {
        User::where('email', $employee->email)->delete();
        Attendance::where('emp_id', $employee->id)->delete();
        $employee->delete();

        flash()->success('Success', 'Employee Account & Records Deleted Successfully!');
        return redirect()->route('employees.index')->with('success');
    }

    public function captureFace(Request $request, Employee $employee)
    {
        try {
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

            $safeName = Str::slug($employee->name); 
            $fileName = $safeName . '_' . $employee->id . '_' . time() . '.jpeg';

            try {
                Storage::disk('s3')->put('faces/' . $fileName, $imageData);
            } catch (\Exception $s3Exception) {
                Log::error('S3 Upload Failed: ' . $s3Exception->getMessage());
            }

            try {
                $rekognition = new RekognitionClient([
                    'region'    => env('AWS_DEFAULT_REGION', 'ap-south-1'),
                    'version'   => 'latest',
                ]);

                $result = $rekognition->indexFaces([
                    'CollectionId' => 'pragnaware-employee-faces',
                    'Image' => ['Bytes' => $imageData],
                    'ExternalImageId' => (string) $employee->id,
                    'DetectionAttributes' => ['DEFAULT']
                ]);

                if (empty($result['FaceRecords'])) {
                    return response()->json([
                        'status' => false,
                        'message' => 'No Face Detected! Please try in a well-lit area.'
                    ]);
                }
            } catch (\Exception $rekognitionException) {
                Log::error('AWS Rekognition Failed: ' . $rekognitionException->getMessage());
            }

            $attendance = new Attendance();
            $attendance->emp_id = $employee->id;
            $attendance->attendance_date = now()->toDateString();
            $attendance->attendance_time = now()->toTimeString();
            $attendance->state = 0;
            $attendance->status = 1;
            $attendance->type = 0;
            $attendance->save();

            return response()->json([
                'status' => true,
                'message' => 'Face securely registered and indexed in AWS Rekognition!',
                'file' => $fileName,
                'path' => 'faces/' . $fileName
            ]);

        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Server Error: ' . $e->getMessage()]);
        }
    }
}