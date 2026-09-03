<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\AuditLogger;

class CameraController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:cameras.view')->only(['index']);
        $this->middleware('permission:cameras.create')->only(['store']);
        $this->middleware('permission:cameras.edit')->only(['update', 'toggleStatus']);
        $this->middleware('permission:cameras.delete')->only('destroy');
    }

    public function index()
    {
        $cameras = DB::table('cameras')->orderBy('created_at', 'desc')->paginate(10);
        $devices = DB::table('finger_devices')->where('status', 'Online')->get();
        return view('admin.cameras.index', compact('cameras', 'devices'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'camera_name' => 'required|string|max:255',
            'camera_id' => 'required|string|unique:cameras,camera_id',
            'location' => 'required|string',
            'ip' => 'required|string',
            'camera_type' => 'required|string',
            'device_id' => 'nullable|string',
            'assigned_department' => 'nullable|string'
        ]);

        DB::beginTransaction();
        try {
            $id = DB::table('cameras')->insertGetId([
                'camera_name' => $request->camera_name,
                'camera_id' => $request->camera_id,
                'location' => $request->location,
                'ip' => $request->ip,
                'camera_type' => $request->camera_type,
                'device_id' => $request->device_id,
                'assigned_department' => $request->assigned_department,
                'status' => 'Connected',
                'last_seen' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            AuditLogger::log(
                'CAMERA_REGISTERED',
                'Device Management',
                "Registered security camera: {$request->camera_name} (ID: {$request->camera_id}, IP: {$request->ip})",
                'Camera',
                $id,
                null,
                $request->except('_token')
            );

            DB::commit();
            flash()->success('Success', 'Security Camera registered successfully!');
            return redirect()->route('cameras.index');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to register camera: ' . $e->getMessage()])->withInput();
        }
    }

    public function update(Request $request, $id)
    {
        $camera = DB::table('cameras')->where('id', $id)->first();
        if (!$camera) {
            abort(404);
        }

        $request->validate([
            'camera_name' => 'required|string|max:255',
            'camera_id' => "required|string|unique:cameras,camera_id,{$id}",
            'location' => 'required|string',
            'ip' => 'required|string',
            'camera_type' => 'required|string',
            'device_id' => 'nullable|string',
            'assigned_department' => 'nullable|string',
            'status' => 'required|in:Connected,Disconnected,Disabled,Error'
        ]);

        DB::beginTransaction();
        try {
            $beforeData = (array)$camera;

            DB::table('cameras')->where('id', $id)->update([
                'camera_name' => $request->camera_name,
                'camera_id' => $request->camera_id,
                'location' => $request->location,
                'ip' => $request->ip,
                'camera_type' => $request->camera_type,
                'device_id' => $request->device_id,
                'assigned_department' => $request->assigned_department,
                'status' => $request->status,
                'updated_at' => now()
            ]);

            AuditLogger::log(
                'CAMERA_UPDATED',
                'Device Management',
                "Modified camera parameters: {$request->camera_name} (ID: {$request->camera_id})",
                'Camera',
                $id,
                $beforeData,
                $request->except(['_token', '_method'])
            );

            DB::commit();
            flash()->success('Success', 'Camera parameters updated successfully!');
            return redirect()->route('cameras.index');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to update camera parameters: ' . $e->getMessage()])->withInput();
        }
    }

    public function toggleStatus($id)
    {
        $camera = DB::table('cameras')->where('id', $id)->first();
        if (!$camera) {
            return response()->json(['success' => false, 'message' => 'Camera not found.'], 404);
        }

        $newStatus = $camera->status === 'Disabled' ? 'Connected' : 'Disabled';
        
        DB::table('cameras')->where('id', $id)->update([
            'status' => $newStatus,
            'updated_at' => now()
        ]);

        AuditLogger::log(
            $newStatus === 'Connected' ? 'CAMERA_ENABLED' : 'CAMERA_DISABLED',
            'Device Management',
            "Toggled status of camera: {$camera->camera_name} to {$newStatus}",
            'Camera',
            $id,
            ['status' => $camera->status],
            ['status' => $newStatus]
        );

        return response()->json([
            'success' => true,
            'message' => "Camera status toggled to {$newStatus} successfully!"
        ]);
    }

    public function destroy($id)
    {
        $camera = DB::table('cameras')->where('id', $id)->first();
        if (!$camera) {
            abort(404);
        }

        DB::beginTransaction();
        try {
            DB::table('cameras')->where('id', $id)->delete();

            AuditLogger::log(
                'CAMERA_REMOVED',
                'Device Management',
                "Removed security camera: {$camera->camera_name} (ID: {$camera->camera_id})",
                'Camera',
                $id,
                (array)$camera,
                null
            );

            DB::commit();
            flash()->success('Success', 'Camera connection removed from database successfully!');
            return redirect()->route('cameras.index');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to remove camera configuration: ' . $e->getMessage()]);
        }
    }

    /**
     * API Endpoint for fetching real-time or background camera health status
     */
    public function apiHealthStatus()
    {
        $cameras = DB::table('cameras')
            ->select('id', 'camera_id', 'camera_name', 'status', 'branch', 'gate', 'last_seen')
            ->get();
            
        return response()->json([
            'success' => true,
            'data' => $cameras
        ]);
    }
}
