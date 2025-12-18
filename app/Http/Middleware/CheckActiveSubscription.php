<?php
// app/Http/Middleware/CheckActiveSubscription.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckActiveSubscription
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

        // Check if client has active subscription
        if (!$user->hasActiveSubscription()) {
            return redirect()->route('client.subscription.plans')
                ->with('warning', 'Please subscribe to a plan to access this feature.');
        }

        return $next($request);
    }
}