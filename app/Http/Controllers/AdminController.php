<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Department;
use App\Models\Syllabus;
use App\Models\AuditLog;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('status', 'active')->count(),
            'total_departments' => Department::count(),
            'total_syllabi' => Syllabus::count(),
            'pending_syllabi' => Syllabus::where('status', 'submitted')->count(),
            'approved_syllabi' => Syllabus::where('status', 'approved')->count(),
        ];

        // Get recent activity from audit logs
        $recentActivity = AuditLog::with('user')
            ->latest()
            ->limit(10)
            ->get();

        // Get recent pending users
        $pendingUsers = User::where('status', 'pending')
            ->latest()
            ->limit(5)
            ->get();

        // Get recent syllabi submissions
        $recentSyllabi = Syllabus::with('creator')
            ->where('status', 'submitted')
            ->latest()
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentActivity', 'pendingUsers', 'recentSyllabi'));
    }

    public function users(Request $request)
    {
        $query = User::with('departments');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $users = $query->paginate(20);
        return view('admin.users.index', compact('users'));
    }

    public function createUser()
    {
        $roles = User::roles();
        $departments = Department::all();
        return view('admin.users.create', compact('roles', 'departments'));
    }

    public function storeUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|in:admin,cdc,hod,faculty,observer',
            'department_ids' => 'nullable|array',
            'department_ids.*' => 'exists:departments,id',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'status' => 'active',
        ]);
        if (!empty($request->department_ids)) {
            $user->departments()->sync($request->department_ids);
        }

        return redirect()->route('admin.users.index')->with('success', 'User created successfully.');
    }

    public function editUser(User $user)
    {
        $roles = User::roles();
        $departments = Department::all();
        return view('admin.users.edit', compact('user', 'roles', 'departments'));
    }

    public function updateUser(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role' => 'required|in:admin,cdc,hod,faculty,observer',
            'department_ids' => 'nullable|array',
            'department_ids.*' => 'exists:departments,id',
            'status' => 'required|in:pending,active,inactive',
        ]);

        $user->update($request->only(['name', 'email', 'role', 'status']));
        $user->departments()->sync($request->department_ids ?? []);

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
    }

    public function toggleUserStatus(User $user)
    {
        $oldStatus = $user->status;
        $user->status = $user->status === 'active' ? 'inactive' : 'active';
        $user->save();

        // Create notification for the user
        \App\Models\Notification::create([
            'user_id' => $user->id,
            'type' => $user->status === 'active' ? 'account_activated' : 'account_deactivated',
            'title' => $user->status === 'active' ? 'Account Activated' : 'Account Deactivated',
            'message' => $user->status === 'active' 
                ? 'Your account has been activated. You can now log in to the system.'
                : 'Your account has been deactivated. Please contact an administrator.',
            'data' => ['admin_id' => auth()->id()]
        ]);

        return redirect()->back()->with('success', 'User status updated successfully.');
    }

    public function departments()
    {
        $departments = Department::with('head')->paginate(20);
        return view('admin.departments.index', compact('departments'));
    }

    public function createDepartment()
    {
        $heads = User::where('status', 'active')->get();
        return view('admin.departments.create', compact('heads'));
    }

    public function storeDepartment(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:20|unique:departments',
            'head_user_id' => 'nullable|exists:users,id',
        ]);

        Department::create($request->all());

        return redirect()->route('admin.departments')->with('success', 'Department created successfully.');
    }

    public function auditLogs()
    {
        $logs = AuditLog::with(['user', 'syllabus'])->latest()->paginate(50);
        return view('admin.audit.index', compact('logs'));
    }
}
