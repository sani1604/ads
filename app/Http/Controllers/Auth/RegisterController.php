<?php
// app/Http/Controllers/Auth/RegisterController.php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Industry;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        $industries = Industry::active()->ordered()->get();
        
        return view('auth.register', compact('industries'));
    }

    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'company_name' => $request->company_name,
            'industry_id' => $request->industry_id,
            'role' => 'client',
            'is_active' => true,
            'is_onboarded' => false,
        ]);

        event(new Registered($user));

        Auth::login($user);

        ActivityLogService::log(
            'user_registered',
            'New user registered',
            $user,
            ['email' => $user->email],
            $user
        );

        return redirect()->route('onboarding.index');
    }
}