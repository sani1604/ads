<?php
// app/Http/Controllers/Admin/CampaignReportController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CampaignReport;
use App\Models\User;
use Illuminate\Http\Request;

class CampaignReportController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin,manager']);
    }

    /**
     * List all campaign reports
     */
 public function index(Request $request)
{
    $userId    = $request->get('client');
    $platform  = $request->get('platform');
    $startDate = $request->get('start_date', now()->subDays(30)->format('Y-m-d'));
    $endDate   = $request->get('end_date', now()->format('Y-m-d'));

    $query = CampaignReport::with('user')
        ->whereBetween('report_date', [$startDate, $endDate]);

    if ($userId) {
        $query->where('user_id', $userId);
    }

    if ($platform) {
        $query->where('platform', $platform);
    }

    if ($search = $request->get('search')) {
        $query->where('campaign_name', 'like', "%{$search}%");
    }

    // This is what the view expects:
    $dailyReports = $query
        ->orderBy('report_date', 'desc')
        ->paginate(15)
        ->withQueryString();

    // Aggregate stats
    $aggregateStats = \App\Models\CampaignReport::selectRaw('
            SUM(impressions) as total_impressions,
            SUM(clicks)      as total_clicks,
            SUM(leads)       as total_leads,
            SUM(spend)       as total_spend
        ')
        ->when($userId, fn($q) => $q->where('user_id', $userId))
        ->when($platform, fn($q) => $q->where('platform', $platform))
        ->when($startDate && $endDate, fn($q) => $q->whereBetween('report_date', [$startDate, $endDate]))
        ->first();

    $clients = \App\Models\User::clients()->active()->orderBy('name')->get(['id','name','company_name']);

    return view('admin.campaign-reports.index', compact(
        'dailyReports',
        'aggregateStats',
        'clients',
        'startDate',
        'endDate'
    ));
}
    /**
     * Show report details
     */
    public function show(CampaignReport $campaignReport)
    {
        $campaignReport->load(['user', 'subscription.package']);

        return view('admin.campaign-reports.show', compact('campaignReport'));
    }

    /**
     * Show create form
     */
    public function create(Request $request)
    {
        $clients = User::clients()->active()->whereHas('subscriptions', fn($q) => $q->active())->orderBy('name')->get();
        $selectedClient = $request->filled('client') ? User::find($request->client) : null;

        return view('admin.campaign-reports.create', compact('clients', 'selectedClient'));
    }

    /**
     * Store new report
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'platform' => 'required|in:facebook,instagram,google,linkedin',
            'campaign_id' => 'nullable|string|max:255',
            'campaign_name' => 'nullable|string|max:255',
            'report_date' => 'required|date',
            'impressions' => 'required|integer|min:0',
            'reach' => 'nullable|integer|min:0',
            'clicks' => 'required|integer|min:0',
            'link_clicks' => 'nullable|integer|min:0',
            'leads' => 'nullable|integer|min:0',
            'conversions' => 'nullable|integer|min:0',
            'spend' => 'required|numeric|min:0',
            'video_views' => 'nullable|integer|min:0',
            'engagements' => 'nullable|integer|min:0',
        ]);

        $client = User::findOrFail($request->user_id);

        // Calculate metrics
        $impressions = $request->impressions;
        $clicks = $request->clicks;
        $leads = $request->leads ?? 0;
        $spend = $request->spend;

        $ctr = $impressions > 0 ? ($clicks / $impressions) * 100 : 0;
        $cpc = $clicks > 0 ? $spend / $clicks : 0;
        $cpm = $impressions > 0 ? ($spend / $impressions) * 1000 : 0;
        $cpl = $leads > 0 ? $spend / $leads : 0;

        CampaignReport::create([
            'user_id' => $client->id,
            'subscription_id' => $client->activeSubscription?->id,
            'platform' => $request->platform,
            'campaign_id' => $request->campaign_id,
            'campaign_name' => $request->campaign_name,
            'report_date' => $request->report_date,
            'impressions' => $impressions,
            'reach' => $request->reach ?? 0,
            'clicks' => $clicks,
            'link_clicks' => $request->link_clicks ?? 0,
            'ctr' => $ctr,
            'cpc' => $cpc,
            'cpm' => $cpm,
            'cpl' => $cpl,
            'leads' => $leads,
            'conversions' => $request->conversions ?? 0,
            'spend' => $spend,
            'video_views' => $request->video_views ?? 0,
            'engagements' => $request->engagements ?? 0,
        ]);

        return redirect()->route('admin.campaign-reports.index')
            ->with('success', 'Campaign report added successfully.');
    }

    /**
     * Edit report
     */
    public function edit(CampaignReport $campaignReport)
    {
        $campaignReport->load('user');

        return view('admin.campaign-reports.edit', compact('campaignReport'));
    }

    /**
     * Update report
     */
    public function update(Request $request, CampaignReport $campaignReport)
    {
        $request->validate([
            'platform' => 'required|in:facebook,instagram,google,linkedin',
            'campaign_id' => 'nullable|string|max:255',
            'campaign_name' => 'nullable|string|max:255',
            'report_date' => 'required|date',
            'impressions' => 'required|integer|min:0',
            'reach' => 'nullable|integer|min:0',
            'clicks' => 'required|integer|min:0',
            'link_clicks' => 'nullable|integer|min:0',
            'leads' => 'nullable|integer|min:0',
            'conversions' => 'nullable|integer|min:0',
            'spend' => 'required|numeric|min:0',
            'video_views' => 'nullable|integer|min:0',
            'engagements' => 'nullable|integer|min:0',
        ]);

        // Calculate metrics
        $impressions = $request->impressions;
        $clicks = $request->clicks;
        $leads = $request->leads ?? 0;
        $spend = $request->spend;

        $ctr = $impressions > 0 ? ($clicks / $impressions) * 100 : 0;
        $cpc = $clicks > 0 ? $spend / $clicks : 0;
        $cpm = $impressions > 0 ? ($spend / $impressions) * 1000 : 0;
        $cpl = $leads > 0 ? $spend / $leads : 0;

        $campaignReport->update([
            'platform' => $request->platform,
            'campaign_id' => $request->campaign_id,
            'campaign_name' => $request->campaign_name,
            'report_date' => $request->report_date,
            'impressions' => $impressions,
            'reach' => $request->reach ?? 0,
            'clicks' => $clicks,
            'link_clicks' => $request->link_clicks ?? 0,
            'ctr' => $ctr,
            'cpc' => $cpc,
            'cpm' => $cpm,
            'cpl' => $cpl,
            'leads' => $leads,
            'conversions' => $request->conversions ?? 0,
            'spend' => $spend,
            'video_views' => $request->video_views ?? 0,
            'engagements' => $request->engagements ?? 0,
        ]);

        return redirect()->route('admin.campaign-reports.index')
            ->with('success', 'Campaign report updated successfully.');
    }

    /**
     * Delete report
     */
    public function destroy(CampaignReport $campaignReport)
    {
        $campaignReport->delete();

        return back()->with('success', 'Campaign report deleted.');
    }

    /**
     * Bulk import from CSV
     */
    public function import(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'platform' => 'required|in:facebook,instagram,google,linkedin',
            'file' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $client = User::findOrFail($request->user_id);
        $platform = $request->platform;
        $file = $request->file('file');

        $handle = fopen($file->getPathname(), 'r');
        $header = fgetcsv($handle); // Skip header

        $imported = 0;
        $errors = 0;

        while (($row = fgetcsv($handle)) !== false) {
            try {
                // Expected format: Date, Campaign Name, Impressions, Clicks, Leads, Spend
                $impressions = (int) ($row[2] ?? 0);
                $clicks = (int) ($row[3] ?? 0);
                $leads = (int) ($row[4] ?? 0);
                $spend = (float) ($row[5] ?? 0);

                CampaignReport::updateOrCreate(
                    [
                        'user_id' => $client->id,
                        'platform' => $platform,
                        'campaign_name' => $row[1] ?? 'Imported Campaign',
                        'report_date' => \Carbon\Carbon::parse($row[0])->format('Y-m-d'),
                    ],
                    [
                        'subscription_id' => $client->activeSubscription?->id,
                        'impressions' => $impressions,
                        'clicks' => $clicks,
                        'leads' => $leads,
                        'spend' => $spend,
                        'ctr' => $impressions > 0 ? ($clicks / $impressions) * 100 : 0,
                        'cpc' => $clicks > 0 ? $spend / $clicks : 0,
                        'cpm' => $impressions > 0 ? ($spend / $impressions) * 1000 : 0,
                        'cpl' => $leads > 0 ? $spend / $leads : 0,
                    ]
                );
                $imported++;
            } catch (\Exception $e) {
                $errors++;
            }
        }

        fclose($handle);

        $message = "{$imported} reports imported successfully.";
        if ($errors > 0) {
            $message .= " {$errors} rows failed.";
        }

        return back()->with('success', $message);
    }

    /**
     * Show import form
     */
    public function showImportForm()
    {
        $clients = User::clients()->active()->orderBy('name')->get();

        return view('admin.campaign-reports.import', compact('clients'));
    }

    /**
     * Export reports
     */
    public function export(Request $request)
    {
        $query = CampaignReport::with('user');

        if ($request->filled('client')) {
            $query->where('user_id', $request->client);
        }

        if ($request->filled('platform')) {
            $query->where('platform', $request->platform);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('report_date', [$request->start_date, $request->end_date]);
        }

        $reports = $query->orderBy('report_date', 'desc')->get();

        $csv = "Date,Client,Platform,Campaign,Impressions,Clicks,CTR,CPC,Leads,CPL,Spend\n";

        foreach ($reports as $report) {
            $csv .= implode(',', [
                $report->report_date->format('Y-m-d'),
                '"' . ($report->user->company_name ?? $report->user->name) . '"',
                $report->platform,
                '"' . ($report->campaign_name ?? '') . '"',
                $report->impressions,
                $report->clicks,
                number_format($report->ctr, 2) . '%',
                number_format($report->cpc, 2),
                $report->leads,
                number_format($report->cpl, 2),
                number_format($report->spend, 2),
            ]) . "\n";
        }

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename=campaign_reports_' . now()->format('Y-m-d') . '.csv');
    }

    /**
     * Get analytics data (AJAX)
     */
    public function analytics(Request $request)
    {
        $startDate = $request->get('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $clientId = $request->get('client');

        $query = CampaignReport::whereBetween('report_date', [$startDate, $endDate]);

        if ($clientId) {
            $query->where('user_id', $clientId);
        }

        // Daily performance
        $dailyData = $query->clone()
            ->selectRaw('
                report_date,
                SUM(impressions) as impressions,
                SUM(clicks) as clicks,
                SUM(leads) as leads,
                SUM(spend) as spend
            ')
            ->groupBy('report_date')
            ->orderBy('report_date')
            ->get()
            ->keyBy(fn($item) => $item->report_date->format('Y-m-d'))
            ->toArray();

        // Platform breakdown
        $platformData = CampaignReport::whereBetween('report_date', [$startDate, $endDate])
            ->when($clientId, fn($q) => $q->where('user_id', $clientId))
            ->selectRaw('
                platform,
                SUM(impressions) as impressions,
                SUM(clicks) as clicks,
                SUM(leads) as leads,
                SUM(spend) as spend
            ')
            ->groupBy('platform')
            ->get()
            ->keyBy('platform')
            ->toArray();

        // Top campaigns
        $topCampaigns = CampaignReport::whereBetween('report_date', [$startDate, $endDate])
            ->when($clientId, fn($q) => $q->where('user_id', $clientId))
            ->whereNotNull('campaign_name')
            ->selectRaw('
                campaign_name,
                platform,
                SUM(leads) as total_leads,
                SUM(spend) as total_spend
            ')
            ->groupBy('campaign_name', 'platform')
            ->orderByDesc('total_leads')
            ->take(10)
            ->get();

        // Summary
        $summary = CampaignReport::whereBetween('report_date', [$startDate, $endDate])
            ->when($clientId, fn($q) => $q->where('user_id', $clientId))
            ->selectRaw('
                SUM(impressions) as total_impressions,
                SUM(clicks) as total_clicks,
                SUM(leads) as total_leads,
                SUM(spend) as total_spend
            ')
            ->first();

        return response()->json([
            'daily' => $dailyData,
            'platforms' => $platformData,
            'top_campaigns' => $topCampaigns,
            'summary' => $summary,
        ]);
    }
}