<?php
// app/Http/Controllers/Client/ReportController.php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\CampaignReport;
use App\Services\ReportService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    protected ReportService $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->middleware(['auth', 'onboarding', 'subscription']);
        $this->reportService = $reportService;
    }

    /**
     * Show reports overview
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        $startDate = $request->get('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        // Get aggregated stats
        $stats = CampaignReport::getAggregatedStats($user->id, $startDate, $endDate);

        // Get chart data
        $chartData = $this->reportService->getChartData($user, 30);

        // Get platform breakdown
        $platformBreakdown = $this->reportService->getPlatformBreakdown($user, $startDate, $endDate);

        // Get daily reports
        $dailyReports = CampaignReport::where('user_id', $user->id)
            ->whereBetween('report_date', [$startDate, $endDate])
            ->orderBy('report_date', 'desc')
            ->paginate(15);

        return view('client.reports.index', compact(
            'stats',
            'chartData',
            'platformBreakdown',
            'dailyReports',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Get chart data via AJAX
     */
    public function chartData(Request $request)
    {
        $user = auth()->user();
        $days = $request->get('days', 30);

        $chartData = $this->reportService->getChartData($user, $days);

        return response()->json($chartData);
    }

    /**
     * Export report
     */
    public function export(Request $request)
    {
        $user = auth()->user();

        $startDate = $request->get('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        $reports = CampaignReport::where('user_id', $user->id)
            ->whereBetween('report_date', [$startDate, $endDate])
            ->orderBy('report_date')
            ->get();

        $csv = "Date,Platform,Campaign,Impressions,Clicks,CTR,CPC,Leads,Spend\n";

        foreach ($reports as $report) {
            $csv .= implode(',', [
                $report->report_date->format('Y-m-d'),
                $report->platform,
                '"' . $report->campaign_name . '"',
                $report->impressions,
                $report->clicks,
                $report->formatted_ctr,
                $report->cpc,
                $report->leads,
                $report->spend,
            ]) . "\n";
        }

        $filename = "campaign_report_{$startDate}_to_{$endDate}.csv";

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename={$filename}");
    }
}