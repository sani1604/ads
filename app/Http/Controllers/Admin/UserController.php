<?php
// app/Http/Controllers/Admin/UserController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    /**
     * List all admin/manager users
     */
    public function index(Request $request)
    {
        $query = User::whereIn('role', ['admin', 'manager']);

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        $users = $query->latest()->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Store new user
     */
    public function store(Request $request)
    {
             $request->validate([
    'name' => 'required|string|max:255',
    'email' => 'required|email|unique:users,email',
    'phone' => 'nullable|string|max:20',
    'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
    'role' => 'required|in:admin,manager',
    'is_active' => 'boolean',
]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'is_active' => $request->boolean('is_active', true),
            'is_onboarded' => true,
            'email_verified_at' => now(),
        ]);

        ActivityLogService::log(
            'staff_user_created',
            "Staff user created: {$user->name} ({$user->role})",
            $user
        );

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Edit user
     */
    public function edit(User $user)
    {
        if (!in_array($user->role, ['admin', 'manager'])) {
            abort(404);
        }

        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update user
     */
    public function update(Request $request, User $user)
    {
        if (!in_array($user->role, ['admin', 'manager'])) {
            abort(404);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'password' => ['nullable', Password::min(8)->mixedCase()->numbers()],
            'role' => 'required|in:admin,manager',
            'is_active' => 'boolean',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'role' => $request->role,
            'is_active' => $request->boolean('is_active', true),
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Delete user
     */
    public function destroy(User $user)
    {
        if (!in_array($user->role, ['admin', 'manager'])) {
            abort(404);
        }

        // Prevent deleting self
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        // Prevent deleting last admin
        if ($user->role === 'admin' && User::where('role', 'admin')->count() <= 1) {
            return back()->with('error', 'Cannot delete the last admin user.');
        }

        $userName = $user->name;
        $user->delete();

        ActivityLogService::log(
            'staff_user_deleted',
            "Staff user deleted: {$userName}",
            null
        );

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }

    /**
     * Toggle user status
     */
    public function toggleStatus(User $user)
    {
        if (!in_array($user->role, ['admin', 'manager'])) {
            abort(404);
        }

        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "User {$status} successfully.");
    }
}