<?php
// app/Http/Controllers/Auth/LoginController.php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogService;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected function redirectTo(): string
    {
        $user = auth()->user();

        if ($user->isStaff()) {
            return route('admin.dashboard');
        }

        if (!$user->is_onboarded) {
            return route('onboarding.index');
        }

        return route('client.dashboard');
    }

    protected function authenticated(Request $request, $user)
    {
        $user->update(['last_login_at' => now()]);
        
        ActivityLogService::userLogin($user);

        if ($user->isStaff()) {
            return redirect()->route('admin.dashboard');
        }

        if (!$user->is_onboarded) {
            return redirect()->route('onboarding.index');
        }

        return redirect()->route('client.dashboard');
    }

    protected function loggedOut(Request $request)
    {
        return redirect()->route('login');
    }

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }
}