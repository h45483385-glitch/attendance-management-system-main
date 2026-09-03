<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class AuditLogController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:audit_logs.view');
    }

    public function index(Request $request)
    {
        $query = DB::table('audit_logs');

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('actor_name', 'like', "%{$search}%")
                  ->orWhere('action', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        if ($request->filled('actor')) {
            $query->where('actor_name', $request->input('actor'));
        }

        if ($request->filled('action_type')) {
            $query->where('action', $request->input('action_type'));
        }

        if ($request->filled('module')) {
            $query->where('module', $request->input('module'));
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [
                $request->input('start_date') . ' 00:00:00',
                $request->input('end_date') . ' 23:59:59'
            ]);
        }

        // Get audit logs, order by latest
        $logs = $query->orderBy('created_at', 'desc')->paginate(15);

        // Fetch distinct filter options with caching
        $actors = cache()->remember('audit_actors', 3600, function () {
            return DB::table('audit_logs')->distinct()->pluck('actor_name');
        });
        $actions = cache()->remember('audit_actions', 3600, function () {
            return DB::table('audit_logs')->distinct()->pluck('action');
        });
        $modules = cache()->remember('audit_modules', 3600, function () {
            return DB::table('audit_logs')->distinct()->pluck('module');
        });

        return view('admin.audit_logs.index', compact('logs', 'actors', 'actions', 'modules'));
    }
}
