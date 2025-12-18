<?php
// app/Http/Controllers/Admin/DashboardController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Creative;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Subscription;
use App\Models\SupportTicket;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin,manager']);
    }

    public function index()
    {
        // Overview Stats
        $stats = [
            'total_clients' => User::clients()->count(),
            'active_clients' => User::clients()->active()->whereHas('subscriptions', fn($q) => $q->active())->count(),
            'total_revenue' => Transaction::completed()->sum('total_amount'),
            'monthly_revenue' => Transaction::completed()->thisMonth()->sum('total_amount'),
            'active_subscriptions' => Subscription::active()->count(),
            'pending_creatives' => Creative::pendingApproval()->count(),
            'new_leads_today' => Lead::today()->count(),
            'open_tickets' => SupportTicket::open()->count(),
        ];

        // Revenue Chart Data (Last 12 months)
        $revenueChart = $this->getRevenueChartData();

        // Recent Activities
        $recentClients = User::clients()
            ->latest()
            ->take(5)
            ->get();

        $recentTransactions = Transaction::with('user')
            ->completed()
            ->latest()
            ->take(5)
            ->get();

        $pendingCreatives = Creative::with(['user', 'files'])
            ->pendingApproval()
            ->latest()
            ->take(5)
            ->get();

        $recentLeads = Lead::with('user')
            ->latest()
            ->take(5)
            ->get();

        // Expiring Subscriptions (Next 7 days)
        $expiringSubscriptions = Subscription::with(['user', 'package'])
            ->expiring(7)
            ->get();

        return view('admin.dashboard', compact(
            'stats',
            'revenueChart',
            'recentClients',
            'recentTransactions',
            'pendingCreatives',
            'recentLeads',
            'expiringSubscriptions'
        ));
    }

    protected function getRevenueChartData(): array
    {
        $labels = [];
        $data = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $labels[] = $date->format('M Y');
            
            $data[] = Transaction::completed()
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->sum('total_amount');
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    public function getStats(Request $request)
    {
        $period = $request->get('period', 'today');

        $query = Transaction::completed();

        switch ($period) {
            case 'today':
                $query->whereDate('created_at', today());
                break;
            case 'week':
                $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                break;
            case 'month':
                $query->thisMonth();
                break;
            case 'year':
                $query->whereYear('created_at', now()->year);
                break;
        }

        return response()->json([
            'revenue' => $query->sum('total_amount'),
            'transactions' => $query->count(),
            'leads' => Lead::when($period === 'today', fn($q) => $q->today())
                ->when($period === 'week', fn($q) => $q->thisWeek())
                ->when($period === 'month', fn($q) => $q->thisMonth())
                ->count(),
        ]);
    }
}