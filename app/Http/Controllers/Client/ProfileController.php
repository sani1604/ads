<?php
// app/Http/Controllers/Client/ProfileController.php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Industry;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'onboarding']);
    }

    /**
     * Show profile edit form
     */
    public function edit()
    {
        $user = auth()->user();
        $industries = Industry::active()->ordered()->get();

        return view('client.profile.edit', compact('user', 'industries'));
    }

    /**
     * Update profile
     */
    public function update(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|regex:/^[6-9]\d{9}$/|unique:users,phone,' . $user->id,
            'company_name' => 'nullable|string|max:255',
            'company_website' => 'nullable|url|max:255',
            'industry_id' => 'nullable|exists:industries,id',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|regex:/^\d{6}$/',
            'gst_number' => 'nullable|string|max:20',
        ]);

        $changes = [];
        foreach ($request->only(['name', 'phone', 'company_name', 'city', 'state']) as $key => $value) {
            if ($user->$key !== $value) {
                $changes[$key] = ['old' => $user->$key, 'new' => $value];
            }
        }

        $user->update($request->only([
            'name', 'phone', 'company_name', 'company_website',
            'industry_id', 'address', 'city', 'state', 'postal_code', 'gst_number'
        ]));

        if (!empty($changes)) {
            ActivityLogService::profileUpdated($user, $changes);
        }

        return back()->with('success', 'Profile updated successfully.');
    }

    /**
     * Update avatar
     */
    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $user = auth()->user();

        // Delete old avatar
        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        // Store new avatar
        $path = $request->file('avatar')->store('avatars', 'public');

        $user->update(['avatar' => $path]);

        return back()->with('success', 'Profile picture updated successfully.');
    }

    /**
     * Change password
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|current_password',
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ]);

        $user = auth()->user();

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        ActivityLogService::passwordChanged($user);

        return back()->with('success', 'Password changed successfully.');
    }

    /**
     * Show activity log
     */
    public function activity()
    {
        $user = auth()->user();

        $activities = $user->activityLogs()
            ->latest()
            ->paginate(20);

        return view('client.profile.activity', compact('activities'));
    }
}