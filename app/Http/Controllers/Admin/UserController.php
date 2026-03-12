<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()->with('departments');

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $departments = Department::where('is_active', true)->get();
        $roles = User::roles();
        return view('admin.users.create', compact('departments', 'roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:12',
            'role' => 'required|in:admin,cdc,hod,faculty,observer',
            'department_ids' => 'nullable|array',
            'department_ids.*' => 'exists:departments,id',
            'status' => 'required|in:pending,active,inactive',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $departmentIds = $validated['department_ids'] ?? [];
        unset($validated['department_ids']);

        $user = User::create($validated);
        if (!empty($departmentIds)) {
            $user->departments()->sync($departmentIds);
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        $departments = Department::where('is_active', true)->get();
        $roles = User::roles();
        return view('admin.users.edit', compact('user', 'departments', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:admin,cdc,hod,faculty,observer',
            'department_ids' => 'nullable|array',
            'department_ids.*' => 'exists:departments,id',
            'status' => 'required|in:pending,active,inactive',
        ]);

        $departmentIds = $validated['department_ids'] ?? [];
        unset($validated['department_ids']);

        $user->update($validated);
        $user->departments()->sync($departmentIds);

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->back()
                ->with('error', 'You cannot delete your own account.');
        }

        $user->status = User::STATUS_INACTIVE;
        $user->save();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deactivated successfully.');
    }

    public function resetPassword(Request $request, User $user)
    {
        $request->validate([
            'password' => 'required|string|min:12|confirmed',
        ]);

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->back()
            ->with('success', 'Password reset successfully.');
    }

    public function approve(User $user)
    {
        if ($user->status !== User::STATUS_PENDING) {
            return redirect()->back()
                ->with('error', 'User is not pending approval.');
        }

        $user->update(['status' => User::STATUS_ACTIVE]);

        // Create notification for the user
        $user->notifications()->create([
            'type' => 'user_approved',
            'title' => 'Account Approved',
            'message' => 'Your account has been approved. You can now access the system.',
            'data' => ['approved_by' => auth()->id()],
        ]);

        return redirect()->back()
            ->with('success', 'User approved successfully.');
    }

    public function reject(User $user)
    {
        if ($user->status !== User::STATUS_PENDING) {
            return redirect()->back()
                ->with('error', 'User is not pending approval.');
        }

        $user->update(['status' => User::STATUS_INACTIVE]);

        // Create notification for the user
        $user->notifications()->create([
            'type' => 'user_rejected',
            'title' => 'Account Rejected',
            'message' => 'Your account registration has been rejected.',
            'data' => ['rejected_by' => auth()->id()],
        ]);

        return redirect()->back()
            ->with('success', 'User rejected successfully.');
    }

    public function toggleStatus(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->back()
                ->with('error', 'You cannot modify your own status.');
        }

        $newStatus = $user->status === User::STATUS_ACTIVE ? User::STATUS_INACTIVE : User::STATUS_ACTIVE;
        $user->update(['status' => $newStatus]);

        $action = $newStatus === User::STATUS_ACTIVE ? 'activated' : 'deactivated';

        // Create notification for the user
        $user->notifications()->create([
            'type' => 'user_status_changed',
            'title' => 'Account Status Changed',
            'message' => "Your account has been {$action}.",
            'data' => ['changed_by' => auth()->id(), 'new_status' => $newStatus],
        ]);

        return redirect()->back()
            ->with('success', "User {$action} successfully.");
    }
}
