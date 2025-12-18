<?php
// app/Services/ReportService.php

namespace App\Services;

use App\Models\CampaignReport;
use App\Models\Lead;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ReportService
{
    /**
     * Get dashboard stats for client
     */
    public function getDashboardStats(User $user): array
    {
        $thisMonth = now()->startOfMonth();
        $lastMonth = now()->subMonth()->startOfMonth();
        $lastMonthEnd = now()->subMonth()->endOfMonth();

        // This month stats
        $thisMonthReports = CampaignReport::where('user_id', $user->id)
            ->where('report_date', '>=', $thisMonth)
            ->get();

        // Last month stats for comparison
        $lastMonthReports = CampaignReport::where('user_id', $user->id)
            ->whereBetween('report_date', [$lastMonth, $lastMonthEnd])
            ->get();

        // Lead stats
        $leadsThisMonth = Lead::where('user_id', $user->id)
            ->where('created_at', '>=', $thisMonth)
            ->count();

        $leadsLastMonth = Lead::where('user_id', $user->id)
            ->whereBetween('created_at', [$lastMonth, $lastMonthEnd])
            ->count();

        return [
            'this_month' => [
                'impressions' => $thisMonthReports->sum('impressions'),
                'clicks' => $thisMonthReports->sum('clicks'),
                'leads' => $leadsThisMonth,
                'spend' => $thisMonthReports->sum('spend'),
                'cpl' => $leadsThisMonth > 0 
                    ? round($thisMonthReports->sum('spend') / $leadsThisMonth, 2) 
                    : 0,
                'ctr' => $thisMonthReports->sum('impressions') > 0
                    ? round(($thisMonthReports->sum('clicks') / $thisMonthReports->sum('impressions')) * 100, 2)
                    : 0,
            ],
            'last_month' => [
                'impressions' => $lastMonthReports->sum('impressions'),
                'clicks' => $lastMonthReports->sum('clicks'),
                'leads' => $leadsLastMonth,
                'spend' => $lastMonthReports->sum('spend'),
            ],
            'changes' => [
                'impressions' => $this->calculateChange(
                    $lastMonthReports->sum('impressions'),
                    $thisMonthReports->sum('impressions')
                ),
                'clicks' => $this->calculateChange(
                    $lastMonthReports->sum('clicks'),
                    $thisMonthReports->sum('clicks')
                ),
                'leads' => $this->calculateChange($leadsLastMonth, $leadsThisMonth),
                'spend' => $this->calculateChange(
                    $lastMonthReports->sum('spend'),
                    $thisMonthReports->sum('spend')
                ),
            ],
        ];
    }

    /**
     * Calculate percentage change
     */
    protected function calculateChange($old, $new): float
    {
        if ($old == 0) {
            return $new > 0 ? 100 : 0;
        }

        return round((($new - $old) / $old) * 100, 2);
    }

    /**
     * Get chart data for dashboard
     */
    public function getChartData(User $user, int $days = 30): array
    {
        $startDate = now()->subDays($days)->startOfDay();
        $endDate = now()->endOfDay();

        $reports = CampaignReport::where('user_id', $user->id)
            ->whereBetween('report_date', [$startDate, $endDate])
            ->orderBy('report_date')
            ->get()
            ->groupBy(fn($r) => $r->report_date->format('Y-m-d'));

        $leads = Lead::where('user_id', $user->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get()
            ->groupBy(fn($l) => $l->created_at->format('Y-m-d'));

        $labels = [];
        $impressions = [];
        $clicks = [];
        $leadsData = [];
        $spend = [];

        $currentDate = $startDate->copy();
        while ($currentDate <= $endDate) {
            $dateKey = $currentDate->format('Y-m-d');
            $labels[] = $currentDate->format('M d');
            
            $dayReports = $reports[$dateKey] ?? collect();
            $impressions[] = $dayReports->sum('impressions');
            $clicks[] = $dayReports->sum('clicks');
            $spend[] = $dayReports->sum('spend');
            $leadsData[] = isset($leads[$dateKey]) ? $leads[$dateKey]->count() : 0;

            $currentDate->addDay();
        }

        return [
            'labels' => $labels,
            'datasets' => [
                'impressions' => $impressions,
                'clicks' => $clicks,
                'leads' => $leadsData,
                'spend' => $spend,
            ],
        ];
    }

    /**
     * Get platform-wise breakdown
     */
    public function getPlatformBreakdown(User $user, ?string $startDate = null, ?string $endDate = null): array
    {
        $query = CampaignReport::where('user_id', $user->id);

        if ($startDate && $endDate) {
            $query->whereBetween('report_date', [$startDate, $endDate]);
        } else {
            $query->where('report_date', '>=', now()->subDays(30));
        }

        return $query->get()
            ->groupBy('platform')
            ->map(function ($reports, $platform) {
                return [
                    'platform' => $platform,
                    'impressions' => $reports->sum('impressions'),
                    'clicks' => $reports->sum('clicks'),
                    'leads' => $reports->sum('leads'),
                    'spend' => $reports->sum('spend'),
                    'ctr' => $reports->sum('impressions') > 0
                        ? round(($reports->sum('clicks') / $reports->sum('impressions')) * 100, 2)
                        : 0,
                    'cpl' => $reports->sum('leads') > 0
                        ? round($reports->sum('spend') / $reports->sum('leads'), 2)
                        : 0,
                ];
            })
            ->values()
            ->toArray();
    }
}