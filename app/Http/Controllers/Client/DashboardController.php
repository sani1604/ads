<?php
// app/Http/Controllers/Client/DashboardController.php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Creative;
use App\Models\Lead;
use App\Services\ReportService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected ReportService $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->middleware(['auth', 'onboarding']);
        $this->reportService = $reportService;
    }

    public function index()
    {
        $user = auth()->user();
        
        // Get dashboard stats
        $stats = $this->reportService->getDashboardStats($user);
        
        // Get chart data
        $chartData = $this->reportService->getChartData($user, 30);
        
        // Get recent leads
        $recentLeads = Lead::where('user_id', $user->id)
            ->latest()
            ->take(5)
            ->get();
        
        // Get pending creatives
        $pendingCreatives = Creative::where('user_id', $user->id)
            ->whereIn('status', ['pending_approval', 'changes_requested'])
            ->latest()
            ->take(5)
            ->get();
        
        // Get active subscription
        $subscription = $user->activeSubscription;
        
        // Get unread notifications count
        $unreadNotifications = $user->getUnreadNotificationsCount();

        return view('client.dashboard', compact(
            'user',
            'stats',
            'chartData',
            'recentLeads',
            'pendingCreatives',
            'subscription',
            'unreadNotifications'
        ));
    }
}