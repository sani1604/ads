<?php
// app/Http/Middleware/CheckOnboarding.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckOnboarding
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Skip for admin/manager
        if ($user->isStaff()) {
            return $next($request);
        }

        // Check if client needs onboarding
        if (!$user->is_onboarded && !$request->routeIs('onboarding.*')) {
            return redirect()->route('onboarding.index');
        }

        return $next($request);
    }
}