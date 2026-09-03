<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\DB;

class RolePermissionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:roles.view')->only(['index']);
        $this->middleware('permission:permissions.manage')->only(['update']);
    }

    public function index()
    {
        $roles = Role::all();
        
        // Define all permissions available in the system
        $availablePermissions = [
            'User Management' => [
                'users.view' => 'View Users',
                'users.create' => 'Create Users',
                'users.edit' => 'Edit / Status Users',
                'users.delete' => 'Delete Users',
            ],
            'Employees' => [
                'employees.view' => 'View Employees',
                'employees.create' => 'Create Employees',
                'employees.edit' => 'Edit Employees',
                'employees.delete' => 'Delete Employees',
            ],
            'Biometrics & Face ID' => [
                'face_enrollment.view' => 'View Face Enrollment',
                'face_enrollment.create' => 'Capture Face ID',
                'face_enrollment.update' => 'Sync Face Biometrics',
            ],
            'Attendance & Logs' => [
                'attendance.view' => 'View Attendance',
                'attendance.correct' => 'Apply Correction / Penalities',
            ],
            'Reports Hub' => [
                'reports.view' => 'View Reports',
                'reports.export' => 'Export Reports (Excel/PDF)',
            ],
            'Biometric Devices' => [
                'devices.view' => 'View Devices',
                'devices.create' => 'Register Devices',
                'devices.edit' => 'Edit / Block Devices',
                'devices.delete' => 'Delete Devices',
            ],
            'Security & Audits' => [
                'roles.view' => 'View Roles',
                'permissions.manage' => 'Manage Role Permissions',
                'audit_logs.view' => 'View Security Audit Logs',
                'security.view' => 'View Security Dashboard',
                'security.manage' => 'Update Security Settings',
            ]
        ];

        return view('admin.roles.index', compact('roles', 'availablePermissions'));
    }

    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        // System Administrator permissions cannot be modified
        if ($role->slug === 'admin') {
            return back()->withErrors(['error' => 'System Administrator permissions are hardcoded to full access and cannot be altered.']);
        }

        $request->validate([
            'permissions' => 'nullable|array',
            'permissions.*' => 'string'
        ]);

        DB::beginTransaction();
        try {
            $beforePermissions = $role->permissions;
            $newPermissions = $request->input('permissions', []);

            $role->permissions = $newPermissions;
            $role->save();

            AuditLogger::log(
                'PERMISSION_CHANGED',
                'Security',
                "Modified permissions of Role: {$role->name}",
                Role::class,
                $role->id,
                ['permissions' => $beforePermissions],
                ['permissions' => $newPermissions]
            );

            DB::commit();
            flash()->success('Success', "Permissions updated successfully for role: {$role->name}!");
            return redirect()->route('roles.index');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed updating role permissions: ' . $e->getMessage()]);
        }
    }
}
