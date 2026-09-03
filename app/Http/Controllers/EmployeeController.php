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
use Aws\Exception\CredentialsException;
use Illuminate\Support\Facades\Log;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = Employee::with('schedules');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('id', 'LIKE', "%{$search}%");
            });
        }

        // Department filter
        if ($request->filled('department')) {
            $query->where('department', $request->department);
        }

        // Position filter
        if ($request->filled('position')) {
            $query->where('position', $request->position);
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Biometric filter
        if ($request->filled('biometric')) {
            $biometric = $request->biometric;
            if ($biometric === 'face') {
                $query->where('face_enrolled', true);
            } elseif ($biometric === 'fingerprint') {
                $query->where('fingerprint_enrolled', true);
            } elseif ($biometric === 'both') {
                $query->where('face_enrolled', true)->where('fingerprint_enrolled', true);
            } elseif ($biometric === 'none') {
                $query->where('face_enrolled', false)->where('fingerprint_enrolled', false);
            }
        }

        // Get distinct filters dynamically for dropdowns
        $departments = Employee::whereNotNull('department')->where('department', '!=', '')->distinct()->pluck('department');
        $positions = Employee::whereNotNull('position')->where('position', '!=', '')->distinct()->pluck('position');
        $statuses = ['Active', 'Inactive'];

        // Paginate
        $employees = $query->paginate(10)->withQueryString();

        // Stats summary calculation
        $totalCount = Employee::count();
        $activeCount = Employee::where('status', 'Active')->count();
        $inactiveCount = Employee::where('status', 'Inactive')->count();
        $faceCount = Employee::where('face_enrolled', true)->count();
        $fingerprintCount = Employee::where('fingerprint_enrolled', true)->count();

        return view('admin.employee')->with([
            'employees' => $employees,
            'schedules' => Schedule::all(),
            'departments' => $departments,
            'positions' => $positions,
            'statuses' => $statuses,
            'stats' => [
                'total' => $totalCount,
                'active' => $activeCount,
                'inactive' => $inactiveCount,
                'face' => $faceCount,
                'fingerprint' => $fingerprintCount,
            ]
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'pin_code' => 'required|min:4',
            'department' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'status' => 'nullable|string',
            'schedule' => 'nullable',
            'schedule_id' => 'nullable'
        ]);

        // 1. Create Login Account
        $user = new User;
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = bcrypt($request->pin_code);
        $user->status = $request->status ?? 'Active';
        
        $position = strtolower($request->position ?? '');
        if (str_contains($position, 'admin')) {
            $user->role = 'admin';
        } elseif (str_contains($position, 'reception') || str_contains($position, 'security')) {
            $user->role = 'receptionist';
        } else {
            $user->role = 'employee';
        }
        $user->save();

        // 2. Create Employee Profile
        $employee = new Employee;
        $employee->name = $request->name;
        $employee->department = $request->department ?? 'General';
        $employee->position = $request->position ?? 'Staff';
        $employee->employment_type = $request->employment_type ?? 'Permanent';
        $employee->email = $request->email;
        $employee->status = $request->status ?? 'Active';
        $employee->pin_code = bcrypt($request->pin_code);
        $employee->save();

        // 3. Sync Schedule Pivot (supports schedule_id or schedule slug/id)
        $schedule = null;
        if ($request->filled('schedule_id')) {
            $schedule = Schedule::find($request->schedule_id);
        } elseif ($request->filled('schedule')) {
            $schedule = is_numeric($request->schedule)
                ? Schedule::find($request->schedule)
                : Schedule::where('slug', $request->schedule)->first();
        }

        if ($schedule) {
            $employee->schedules()->sync([$schedule->id]);
        }

        // 4. Auto-sync with Salary Master
        if ($request->position) {
            SalaryMaster::firstOrCreate(
                ['designation' => $request->position],
                [
                    'department' => $request->department ?? 'General', 
                    'current_base_salary' => (str_contains(strtolower($request->position), 'intern')) ? 15000 : 30000, 
                ]
            );
        }

        flash()->success('Success', 'Employee Account, Department & Shift Created Successfully!');
        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Employee Account, Department & Shift Created Successfully!']);
        }
        return redirect()->route('employees.index')->with('success');
    }

    public function update(Request $request, Employee $employee)
    {
        $user = User::where('email', $employee->getOriginal('email'))->first();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . ($user ? $user->id : 'NULL') . '|unique:employees,email,' . $employee->id,
            'department' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'status' => 'nullable|string',
            'schedule' => 'nullable',
            'schedule_id' => 'nullable',
            'pin_code' => 'nullable|min:4'
        ]);

        // Direct Update
        $employee->name = $request->name;
        if ($request->has('department')) {
            $employee->department = $request->department ?? 'General';
        }
        if ($request->has('position')) {
            $employee->position = $request->position ?? 'Staff';
        }
        if ($request->has('employment_type')) {
            $employee->employment_type = $request->employment_type ?? 'Permanent';
        }
        if ($request->has('status')) {
            $employee->status = $request->status;
        }
        $employee->email = $request->email;
        
        if ($request->filled('pin_code')) {
            $employee->pin_code = bcrypt($request->pin_code);
        }
        $employee->save();

        // Sync Schedule Pivot (supports schedule_id or schedule slug/id)
        $schedule = null;
        if ($request->filled('schedule_id')) {
            $schedule = Schedule::find($request->schedule_id);
        } elseif ($request->filled('schedule')) {
            $schedule = is_numeric($request->schedule)
                ? Schedule::find($request->schedule)
                : Schedule::where('slug', $request->schedule)->first();
        }

        if ($schedule) {
            $employee->schedules()->sync([$schedule->id]);
        }

        // Update corresponding User table
        if ($user) {
            $userData = [
                'name' => $request->name,
                'email' => $request->email,
            ];
            if ($request->filled('pin_code')) {
                $userData['password'] = bcrypt($request->pin_code);
            }
            if ($request->has('status')) {
                $userData['status'] = $request->status;
            }
            $user->update($userData);
        }

        // Auto-sync with Salary Master if position is updated
        if ($request->filled('position')) {
            SalaryMaster::firstOrCreate(
                ['designation' => $request->position],
                [
                    'department' => $employee->department ?? 'General',
                    'current_base_salary' => (str_contains(strtolower($request->position), 'intern')) ? 15000 : 30000,
                ]
            );
        }

        flash()->success('Success', 'Employee Record, Department & Shift Updated Successfully!');
        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Employee Record, Department & Shift Updated Successfully!']);
        }
        return redirect()->route('employees.index')->with('success');
    }

    public function destroy(Employee $employee)
    {
        User::where('email', $employee->email)->delete();
        Attendance::where('emp_id', $employee->id)->delete();
        $employee->delete();

        flash()->success('Success', 'Employee Account & Records Deleted Successfully!');
        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Employee Account & Records Deleted Successfully!']);
        }
        return redirect()->route('employees.index')->with('success');
    }

    public function captureFace(Request $request, Employee $employee)
    {
        // Guard: fail fast with a clear message if AWS credentials are not configured.
        if (empty(env('AWS_ACCESS_KEY_ID')) || empty(env('AWS_SECRET_ACCESS_KEY'))) {
            Log::warning('AWS credentials are not set in .env — face capture aborted for employee #' . $employee->id);
            return response()->json([
                'status'  => false,
                'message' => 'AWS credentials are not configured. Please add AWS_ACCESS_KEY_ID and AWS_SECRET_ACCESS_KEY to your .env file.'
            ], 503);
        }

        try {
            if (!$request->has('image') || empty($request->image)) {
                return response()->json(['status' => false, 'message' => 'No image data received.']);
            }

            // 1. Decode base64 image data
            $image = $request->image;
            if (strpos($image, 'data:image') !== false) {
                $image = explode(',', $image)[1];
            }
            $image = str_replace(' ', '+', $image);
            $imageData = base64_decode($image);

            if ($imageData === false || empty($imageData)) {
                return response()->json(['status' => false, 'message' => 'Invalid image data received.']);
            }

            // 2. Generate unique filename (face_emp_{id}_{timestamp}.jpg)
            $timestamp = time();
            $fileName = "face_emp_{$employee->id}_{$timestamp}.jpg";
            $s3Path   = 'faces/' . $fileName;

            // 3. Upload directly to AWS S3 bucket
            try {
                Storage::disk('s3')->put($s3Path, $imageData);
                $s3Url = Storage::disk('s3')->url($s3Path);
            } catch (CredentialsException $s3CredEx) {
                Log::error('S3 Credentials Error: ' . $s3CredEx->getMessage());
                return response()->json([
                    'status'  => false,
                    'message' => 'AWS S3 credentials are invalid. Please check AWS_ACCESS_KEY_ID and AWS_SECRET_ACCESS_KEY in .env.'
                ], 503);
            } catch (\Exception $s3Exception) {
                Log::error('S3 Upload Failed: ' . $s3Exception->getMessage());
                return response()->json([
                    'status'  => false,
                    'message' => 'Failed to upload face image to S3: ' . $s3Exception->getMessage()
                ], 500);
            }

            // 4. Index face with AWS Rekognition (if collection exists)
            try {
                $rekognition = new RekognitionClient([
                    'region'      => env('AWS_DEFAULT_REGION', 'ap-south-1'),
                    'version'     => 'latest',
                    'credentials' => [
                        'key'    => env('AWS_ACCESS_KEY_ID'),
                        'secret' => env('AWS_SECRET_ACCESS_KEY'),
                    ],
                ]);

                $result = $rekognition->indexFaces([
                    'CollectionId'        => 'pragnaware-employee-faces',
                    'Image'               => ['Bytes' => $imageData],
                    'ExternalImageId'     => (string) $employee->id,
                    'DetectionAttributes' => ['DEFAULT']
                ]);

                if (empty($result['FaceRecords'])) {
                    Log::warning('No face detected by Rekognition for employee #' . $employee->id);
                }
            } catch (\Exception $rekognitionException) {
                Log::warning('AWS Rekognition indexing notice: ' . $rekognitionException->getMessage());
            }

            // 5. Save S3 path and mark face as enrolled
            $employee->face_enrolled   = true;
            $employee->face_photo_path = $s3Url;
            $employee->save();

            // 6. Return JSON response including the uploaded S3 URL
            return response()->json([
                'status'  => true,
                'message' => 'Face biometrics enrolled and stored in AWS S3 successfully!',
                'url'     => $s3Url,
                'path'    => $s3Path,
                'file'    => $fileName
            ]);

        } catch (\Exception $e) {
            Log::error('Face capture error: ' . $e->getMessage());
            return response()->json([
                'status'  => false,
                'message' => 'Server Error: ' . $e->getMessage()
            ], 500);
        }
    }
}