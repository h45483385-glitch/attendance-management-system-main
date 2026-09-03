<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\FingerHelper;
use App\Http\Requests\FingerDevice\StoreRequest;
use App\Http\Requests\FingerDevice\UpdateRequest;
use App\Models\FingerDevices;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Leave;
use Illuminate\Http\RedirectResponse;
use Rats\Zkteco\Lib\ZKTeco;

use App\Services\AuditLogger;

class BiometricDeviceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:devices.view')->only(['index', 'show']);
        $this->middleware('permission:devices.create')->only(['create', 'store']);
        $this->middleware('permission:devices.edit')->only(['edit', 'update', 'block', 'activate']);
        $this->middleware('permission:devices.delete')->only('destroy');
    }

    public function index()
    {
        $devices = FingerDevices::all();
        $totalDevices = $devices->count();
        $connectedDevices = 0;

        foreach ($devices as $device) {
            // Blocked or Inactive devices should bypass connection checks
            if ($device->status === 'Blocked' || $device->status === 'Inactive') {
                $device->is_online = false;
                continue;
            }

            // Fast connection check using fsockopen with 0.5s timeout
            $fp = @fsockopen($device->ip, 4370, $errno, $errstr, 0.5);
            if ($fp) {
                $connectedDevices++;
                fclose($fp);
                $device->is_online = true;
                // Update last seen
                $device->last_seen = now();
                $device->saveQuietly();
            } else {
                $device->is_online = false;
            }
        }

        $offlineDevices = $totalDevices - $connectedDevices;
        $enrolledCount = Employee::where('face_enrolled', true)->orWhere('fingerprint_enrolled', true)->count();
        $pendingCount = Employee::where('face_enrolled', false)->where('fingerprint_enrolled', false)->count();

        return view('admin.fingerDevices.index', [
            'devices' => $devices,
            'stats' => [
                'total' => $totalDevices,
                'connected' => $connectedDevices,
                'offline' => $offlineDevices,
                'enrolled' => $enrolledCount,
                'pending' => $pendingCount,
            ]
        ]);
    }

    public function create()
    {
        return view('admin.fingerDevices.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'ip' => 'required|ipv4',
            'device_id' => 'required|string|unique:finger_devices,device_id',
            'type' => 'required|string',
            'location' => 'required|string'
        ]);

        $helper = new FingerHelper();
        $device = $helper->init($request->input('ip'));

        if ($device->connect()) {
            $serial = $helper->getSerial($device);
            
            // Generate a secure credential token
            $token = bin2hex(random_bytes(20));

            $fingerDevice = FingerDevices::create([
                'name' => $request->name,
                'ip' => $request->ip,
                'serialNumber' => $serial,
                'device_id' => $request->device_id,
                'type' => $request->type,
                'location' => $request->location,
                'status' => 'Online',
                'token' => $token,
                'registered_by' => auth()->id()
            ]);

            AuditLogger::log(
                'DEVICE_REGISTERED',
                'Device Management',
                "Registered attendance device: {$request->name} (ID: {$request->device_id}, IP: {$request->ip})",
                FingerDevices::class,
                $fingerDevice->id,
                null,
                $fingerDevice->toArray()
            );

            flash()->success('Success', 'Biometric Device registered successfully! Secure Token generated.');
        } else {
            flash()->error('Oops', 'Failed connecting to Biometric Device!');
        }

        return redirect()->route('finger_device.index');
    }

    public function show(FingerDevices $fingerDevice)
    {
        return view('admin.fingerDevices.show', compact('fingerDevice'));
    }

    public function edit(FingerDevices $fingerDevice)
    {
        return view('admin.fingerDevices.edit', compact('fingerDevice'));
    }

    public function update(Request $request, FingerDevices $fingerDevice): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'ip' => 'required|ipv4',
            'location' => 'required|string',
            'status' => 'required|in:Online,Offline,Inactive,Blocked'
        ]);

        $beforeData = $fingerDevice->toArray();
        $fingerDevice->update($request->only(['name', 'ip', 'location', 'status']));

        AuditLogger::log(
            'DEVICE_UPDATED',
            'Device Management',
            "Updated attendance device: {$fingerDevice->name} (ID: {$fingerDevice->device_id})",
            FingerDevices::class,
            $fingerDevice->id,
            $beforeData,
            $fingerDevice->toArray()
        );

        flash()->success('Success', 'Biometric Device updated successfully!');
        return redirect()->route('finger_device.index');
    }

    public function block($id): RedirectResponse
    {
        $device = FingerDevices::findOrFail($id);
        $beforeData = $device->toArray();
        
        $device->status = 'Blocked';
        $device->save();

        AuditLogger::log(
            'DEVICE_BLOCKED',
            'Device Management',
            "Blocked attendance device: {$device->name} (ID: {$device->device_id})",
            FingerDevices::class,
            $device->id,
            $beforeData,
            $device->toArray()
        );

        flash()->success('Success', 'Biometric Device has been blocked.');
        return back();
    }

    public function activate($id): RedirectResponse
    {
        $device = FingerDevices::findOrFail($id);
        $beforeData = $device->toArray();
        
        $device->status = 'Online';
        $device->save();

        AuditLogger::log(
            'DEVICE_ACTIVATED',
            'Device Management',
            "Activated attendance device: {$device->name} (ID: {$device->device_id})",
            FingerDevices::class,
            $device->id,
            $beforeData,
            $device->toArray()
        );

        flash()->success('Success', 'Biometric Device has been activated.');
        return back();
    }

    public function destroy(FingerDevices $fingerDevice): RedirectResponse
    {
        $beforeData = $fingerDevice->toArray();

        try {
            $fingerDevice->delete();

            AuditLogger::log(
                'DEVICE_REMOVED',
                'Device Management',
                "Deleted attendance device: {$beforeData['name']} (ID: {$beforeData['device_id']})",
                FingerDevices::class,
                $beforeData['id'],
                $beforeData,
                null
            );

            flash()->success('Success', 'Biometric Device deleted successfully!');
        } catch (\Exception $e) {
            flash()->error('Oops', "Failed to delete {$fingerDevice->name}");
        }

        return back();
    }

    public function addEmployee(FingerDevices $fingerDevice): RedirectResponse
    {
        $device = new ZKTeco($fingerDevice->ip, 4370);
        $device->connect();

        $deviceUsers = collect($device->getUser())->pluck('uid');

        $employees = Employee::select('name', 'id')
            ->whereNotIn('id', $deviceUsers)
            ->get();

        $i = 1;

        foreach ($employees as $employee) {
            $device->setUser($i++, $employee->id, $employee->name, '', '0', '0');
            $employee->fingerprint_enrolled = true;
            $employee->save();
        }

        // Also mark existing device users as fingerprint_enrolled
        Employee::whereIn('id', $deviceUsers)->update(['fingerprint_enrolled' => true]);

        if (request()->ajax()) {
            return response()->json([
                'status' => true,
                'message' => 'All Employees added to Biometric device successfully!'
            ]);
        }

        flash()->success('Success', 'All Employees added to Biometric device successfully!');
        return back();
    }

    public function getAttendance(FingerDevices $fingerDevice)
    {
        $device = new ZKTeco($fingerDevice->ip, 4370);
        $device->connect();

        $data = $device->getAttendance();

        foreach ($data as $value) {

            if ($value['type'] == 0) {

                if ($employee = Employee::whereId($value['id'])->first()) {

                    if (!Attendance::whereAttendance_date(date('Y-m-d', strtotime($value['timestamp'])))
                        ->whereEmp_id($value['id'])
                        ->whereType(0)
                        ->first()) {

                        $att = new Attendance();
                        $att->uid = $value['uid'];
                        $att->emp_id = $value['id'];
                        $att->state = $value['state'];
                        $att->attendance_time = date('H:i:s', strtotime($value['timestamp']));
                        $att->attendance_date = date('Y-m-d', strtotime($value['timestamp']));
                        $att->type = $value['type'];

                        $schedule = $employee->schedules->first();
                        $employeeStartTime = $schedule ? $schedule->time_in : '09:30:00';
                        $employeeEndTime = $schedule ? $schedule->time_out : '18:30:00';

                        if ($att->attendance_time > $employeeEndTime) {
                            continue;
                        }

                        if (!($employeeStartTime >= $att->attendance_time)) {
                            $att->status = 0;
                            AttendanceController::lateTimeDevice($value['timestamp'], $employee);
                        }

                        $att->save();
                    }
                }

            } else {

                if ($employee = Employee::whereId($value['id'])->first()) {

                    if (!Leave::whereLeave_date(date('Y-m-d', strtotime($value['timestamp'])))
                        ->whereEmp_id($value['id'])
                        ->whereType(1)
                        ->first()) {

                        $leave = new Leave();
                        $leave->uid = $value['uid'];
                        $leave->emp_id = $value['id'];
                        $leave->state = $value['state'];
                        $leave->leave_time = date('H:i:s', strtotime($value['timestamp']));
                        $leave->leave_date = date('Y-m-d', strtotime($value['timestamp']));
                        $leave->type = $value['type'];

                        if (!($employee->schedules->first()->time_out <= $leave->leave_time)) {
                            $leave->status = 0;
                        } else {
                            LeaveController::overTimeDevice($value['timestamp'], $employee);
                        }

                        $leave->save();
                    }
                }
            }
        }

        if (request()->ajax()) {
            return response()->json([
                'status' => true,
                'message' => 'Attendance Synced Successfully!'
            ]);
        }

        flash()->success('Success', 'Attendance Synced Successfully!');
        return back();
    }

    // ✅ NEW FUNCTION (MANUAL SYNC)
    public function sync()
    {
        $devices = FingerDevices::all();

        foreach ($devices as $fingerDevice) {

            $device = new ZKTeco($fingerDevice->ip, 4370);

            if (!$device->connect()) {
                continue;
            }

            $data = $device->getAttendance();

            foreach ($data as $value) {

                if ($value['type'] == 0) {

                    if ($employee = Employee::whereId($value['id'])->first()) {

                        if (!Attendance::whereAttendance_date(date('Y-m-d', strtotime($value['timestamp'])))
                            ->whereEmp_id($value['id'])
                            ->whereType(0)
                            ->first()) {

                            $attendanceTime = date('H:i:s', strtotime($value['timestamp']));
                            $schedule = $employee->schedules->first();
                            $employeeStartTime = $schedule ? $schedule->time_in : '09:30:00';
                            $employeeEndTime = $schedule ? $schedule->time_out : '18:30:00';
                            
                            if ($attendanceTime > $employeeEndTime) {
                                continue;
                            }
                            
                            $isLate = ($attendanceTime > $employeeStartTime) ? 0 : 1;

                            Attendance::create([
                                'uid' => $value['uid'],
                                'emp_id' => $value['id'],
                                'state' => $value['state'],
                                'attendance_time' => $attendanceTime,
                                'attendance_date' => date('Y-m-d', strtotime($value['timestamp'])),
                                'type' => $value['type'],
                                'status' => $isLate
                            ]);
                        }
                    }

                } else {

                    if ($employee = Employee::whereId($value['id'])->first()) {

                        if (!Leave::whereLeave_date(date('Y-m-d', strtotime($value['timestamp'])))
                            ->whereEmp_id($value['id'])
                            ->whereType(1)
                            ->first()) {

                            Leave::create([
                                'uid' => $value['uid'],
                                'emp_id' => $value['id'],
                                'state' => $value['state'],
                                'leave_time' => date('H:i:s', strtotime($value['timestamp'])),
                                'leave_date' => date('Y-m-d', strtotime($value['timestamp'])),
                                'type' => $value['type'],
                                'status' => 1
                            ]);
                        }
                    }
                }
            }
        }

        return "Attendance Synced Successfully!";
    }
}