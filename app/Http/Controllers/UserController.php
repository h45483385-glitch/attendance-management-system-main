<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:users.view')->only(['index', 'show']);
        $this->middleware('permission:users.create')->only(['create', 'store']);
        $this->middleware('permission:users.edit')->only(['edit', 'update', 'toggleStatus']);
        $this->middleware('permission:users.delete')->only('destroy');
    }

    public function index()
    {
        $users = User::with('roles')->orderBy('created_at', 'desc')->paginate(10);
        $roles = Role::all();
        return view('admin.users.index', compact('users', 'roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
            'role_id' => 'required|exists:roles,id',
            'status' => 'required|in:Active,Inactive'
        ]);

        DB::beginTransaction();
        try {
            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = Hash::make($request->password);
            $user->status = $request->status;
            $user->save();

            // Link user to the selected role
            $user->roles()->sync([$request->role_id]);

            AuditLogger::log(
                'USER_CREATED',
                'User Management',
                "Created administrative user account: {$user->name} ({$user->email}) with Role ID: {$request->role_id}",
                User::class,
                $user->id,
                null,
                ['name' => $user->name, 'email' => $user->email, 'status' => $user->status]
            );

            DB::commit();
            flash()->success('Success', 'Administrative User created successfully!');
            return redirect()->route('users.index');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to create user account: ' . $e->getMessage()])->withInput();
        }
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => "required|email|unique:users,email,{$id}",
            'password' => 'nullable|min:6|confirmed',
            'role_id' => 'required|exists:roles,id',
            'status' => 'required|in:Active,Inactive'
        ]);

        // Prevent self-deactivation
        if (auth()->id() == $id && $request->status === 'Inactive') {
            return back()->withErrors(['error' => 'You cannot deactivate your own active session account.']);
        }

        DB::beginTransaction();
        try {
            $beforeData = ['name' => $user->name, 'email' => $user->email, 'status' => $user->status];
            
            $user->name = $request->name;
            $user->email = $request->email;
            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }
            $user->status = $request->status;
            $user->save();

            // Sync role mapping
            $user->roles()->sync([$request->role_id]);

            AuditLogger::log(
                'USER_UPDATED',
                'User Management',
                "Updated administrative user account: {$user->name} ({$user->email})",
                User::class,
                $user->id,
                $beforeData,
                ['name' => $user->name, 'email' => $user->email, 'status' => $user->status]
            );

            DB::commit();
            flash()->success('Success', 'User profile updated successfully!');
            return redirect()->route('users.index');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to update user: ' . $e->getMessage()])->withInput();
        }
    }

    public function toggleStatus($id)
    {
        $user = User::findOrFail($id);

        if (auth()->id() == $id) {
            return response()->json(['success' => false, 'message' => 'You cannot deactivate your own logged-in account.'], 403);
        }

        $beforeStatus = $user->status;
        $user->status = $user->status === 'Active' ? 'Inactive' : 'Active';
        $user->save();

        AuditLogger::log(
            $user->status === 'Active' ? 'USER_ACTIVATED' : 'USER_DEACTIVATED',
            'User Management',
            "Toggled user status of {$user->name} from {$beforeStatus} to {$user->status}",
            User::class,
            $user->id,
            ['status' => $beforeStatus],
            ['status' => $user->status]
        );

        return response()->json([
            'success' => true,
            'message' => "User account status toggled to {$user->status} successfully!"
        ]);
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if (auth()->id() == $id) {
            return back()->withErrors(['error' => 'You are currently logged in with this account. Self-deletion is prohibited.']);
        }

        DB::beginTransaction();
        try {
            // Log target name before delete
            $deletedName = $user->name;
            $deletedEmail = $user->email;

            // Clear role users bridge
            $user->roles()->detach();
            $user->delete();

            AuditLogger::log(
                'USER_DELETED',
                'User Management',
                "Deleted administrative user account: {$deletedName} ({$deletedEmail})",
                User::class,
                $id,
                ['name' => $deletedName, 'email' => $deletedEmail],
                null
            );

            DB::commit();
            flash()->success('Success', 'User account deleted successfully!');
            return redirect()->route('users.index');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to delete user: ' . $e->getMessage()]);
        }
    }
}
