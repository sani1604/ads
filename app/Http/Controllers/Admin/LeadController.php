<?php
// app/Http/Controllers/Admin/LeadController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\LeadRequest;
use App\Models\Lead;
use App\Models\User;
use App\Services\LeadService;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    protected LeadService $leadService;

    public function __construct(LeadService $leadService)
    {
        $this->middleware(['auth', 'role:admin,manager']);
        $this->leadService = $leadService;
    }

    /**
     * List all leads
     */
    public function index(Request $request)
    {
        $query = Lead::with(['user', 'subscription.package']);

        // Filter by client
        if ($request->filled('client')) {
            $query->where('user_id', $request->client);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by source
        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        // Filter by quality
        if ($request->filled('quality')) {
            $query->where('quality', $request->quality);
        }

        // Filter by date range
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59',
            ]);
        }

        // Search
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Sorting
        $sortBy = $request->get('sort', 'created_at');
        $sortOrder = $request->get('order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $leads = $query->paginate(25)->withQueryString();

        // Get filter options
        $clients = User::clients()->active()->orderBy('name')->get(['id', 'name', 'company_name']);

        // Stats
        $stats = [
            'total' => Lead::count(),
            'today' => Lead::today()->count(),
            'this_week' => Lead::thisWeek()->count(),
            'this_month' => Lead::thisMonth()->count(),
            'new' => Lead::where('status', 'new')->count(),
            'converted' => Lead::where('status', 'converted')->count(),
        ];

        // Source breakdown
        $sourceBreakdown = Lead::selectRaw('source, COUNT(*) as count')
            ->groupBy('source')
            ->pluck('count', 'source')
            ->toArray();

        return view('admin.leads.index', compact('leads', 'clients', 'stats', 'sourceBreakdown'));
    }

    /**
     * Show lead details
     */
    public function show(Lead $lead)
    {
        $lead->load(['user', 'subscription.package']);

        return view('admin.leads.show', compact('lead'));
    }

    /**
     * Show create form
     */
    public function create(Request $request)
    {
        $clients = User::clients()->active()->orderBy('name')->get();
        $selectedClient = $request->filled('client') ? User::find($request->client) : null;

        return view('admin.leads.create', compact('clients', 'selectedClient'));
    }

    /**
     * Store new lead
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'required|string|max:20',
            'alternate_phone' => 'nullable|string|max:20',
            'source' => 'required|in:facebook,instagram,google,linkedin,website,manual,other',
            'campaign_name' => 'nullable|string|max:255',
            'ad_name' => 'nullable|string|max:255',
            'status' => 'nullable|in:new,contacted,qualified,converted,lost,spam',
            'quality' => 'nullable|in:hot,warm,cold',
            'notes' => 'nullable|string|max:2000',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
        ]);

        $client = User::findOrFail($request->user_id);

        $lead = $this->leadService->create($client, $request->except('user_id'));

        return redirect()->route('admin.leads.show', $lead)
            ->with('success', 'Lead created successfully.');
    }

    /**
     * Show edit form
     */
    public function edit(Lead $lead)
    {
        $lead->load('user');

        return view('admin.leads.edit', compact('lead'));
    }

    /**
     * Update lead
     */
    public function update(Request $request, Lead $lead)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'required|string|max:20',
            'alternate_phone' => 'nullable|string|max:20',
            'source' => 'required|in:facebook,instagram,google,linkedin,website,manual,other',
            'campaign_name' => 'nullable|string|max:255',
            'ad_name' => 'nullable|string|max:255',
            'status' => 'required|in:new,contacted,qualified,converted,lost,spam',
            'quality' => 'nullable|in:hot,warm,cold',
            'notes' => 'nullable|string|max:2000',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
        ]);

        $lead->update($request->only([
            'name', 'email', 'phone', 'alternate_phone', 'source',
            'campaign_name', 'ad_name', 'status', 'quality', 'notes', 'city', 'state'
        ]));

        if ($request->status === 'contacted' && !$lead->contacted_at) {
            $lead->update(['contacted_at' => now()]);
        }

        return redirect()->route('admin.leads.show', $lead)
            ->with('success', 'Lead updated successfully.');
    }

    /**
     * Update status (AJAX)
     */
    public function updateStatus(Request $request, Lead $lead)
    {
        $request->validate([
            'status' => 'required|in:new,contacted,qualified,converted,lost,spam',
        ]);

        $this->leadService->updateStatus($lead, $request->status);

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully.',
            'status_badge' => $lead->fresh()->status_badge,
        ]);
    }

    /**
     * Update quality (AJAX)
     */
    public function updateQuality(Request $request, Lead $lead)
    {
        $request->validate([
            'quality' => 'required|in:hot,warm,cold',
        ]);

        $lead->setQuality($request->quality);

        return response()->json([
            'success' => true,
            'message' => 'Quality updated successfully.',
            'quality_badge' => $lead->fresh()->quality_badge,
        ]);
    }

    /**
     * Bulk update status
     */
    public function bulkUpdateStatus(Request $request)
    {
        $request->validate([
            'lead_ids' => 'required|array|min:1',
            'lead_ids.*' => 'exists:leads,id',
            'status' => 'required|in:new,contacted,qualified,converted,lost,spam',
        ]);

        Lead::whereIn('id', $request->lead_ids)->update([
            'status' => $request->status,
            'contacted_at' => $request->status === 'contacted' ? now() : null,
        ]);

        return back()->with('success', count($request->lead_ids) . ' leads updated.');
    }

    /**
     * Add note to lead
     */
    public function addNote(Request $request, Lead $lead)
    {
        $request->validate([
            'note' => 'required|string|max:2000',
        ]);

        $existingNotes = $lead->notes ?? '';
        $newNote = '[' . now()->format('Y-m-d H:i') . ' - ' . auth()->user()->name . '] ' . $request->note;

        $lead->update([
            'notes' => $existingNotes ? $existingNotes . "\n\n" . $newNote : $newNote,
        ]);

        return back()->with('success', 'Note added successfully.');
    }

    /**
     * Delete lead
     */
    public function destroy(Lead $lead)
    {
        $lead->delete();

        return redirect()->route('admin.leads.index')
            ->with('success', 'Lead deleted successfully.');
    }

    /**
     * Bulk delete
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'lead_ids' => 'required|array|min:1',
            'lead_ids.*' => 'exists:leads,id',
        ]);

        Lead::whereIn('id', $request->lead_ids)->delete();

        return back()->with('success', count($request->lead_ids) . ' leads deleted.');
    }

    /**
     * Export leads
     */
    public function export(Request $request)
    {
        $query = Lead::with('user');

        // Apply same filters as index
        if ($request->filled('client')) {
            $query->where('user_id', $request->client);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [$request->start_date, $request->end_date . ' 23:59:59']);
        }

        $leads = $query->orderBy('created_at', 'desc')->get();

        $csv = "ID,Client,Name,Email,Phone,Source,Campaign,Status,Quality,City,State,Created At\n";

        foreach ($leads as $lead) {
            $csv .= implode(',', [
                $lead->lead_id,
                '"' . ($lead->user->company_name ?? $lead->user->name) . '"',
                '"' . $lead->name . '"',
                $lead->email ?? '',
                $lead->phone ?? '',
                $lead->source,
                '"' . ($lead->campaign_name ?? '') . '"',
                $lead->status,
                $lead->quality ?? '',
                '"' . ($lead->city ?? '') . '"',
                '"' . ($lead->state ?? '') . '"',
                $lead->created_at->format('Y-m-d H:i:s'),
            ]) . "\n";
        }

        $filename = 'leads_export_' . now()->format('Y-m-d_His') . '.csv';

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename={$filename}");
    }

    /**
     * Import leads from CSV
     */
    public function import(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'file' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $client = User::findOrFail($request->user_id);
        $file = $request->file('file');

        $handle = fopen($file->getPathname(), 'r');
        $header = fgetcsv($handle); // Skip header row

        $imported = 0;
        $errors = 0;

        while (($row = fgetcsv($handle)) !== false) {
            try {
                // Assuming CSV format: Name, Email, Phone, Source, Campaign
                $this->leadService->create($client, [
                    'name' => $row[0] ?? 'Unknown',
                    'email' => $row[1] ?? null,
                    'phone' => $row[2] ?? null,
                    'source' => $row[3] ?? 'manual',
                    'campaign_name' => $row[4] ?? null,
                ]);
                $imported++;
            } catch (\Exception $e) {
                $errors++;
            }
        }

        fclose($handle);

        $message = "{$imported} leads imported successfully.";
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

        return view('admin.leads.import', compact('clients'));
    }

    /**
     * Assign leads to client (Transfer)
     */
    public function assignToClient(Request $request)
    {
        $request->validate([
            'lead_ids' => 'required|array|min:1',
            'lead_ids.*' => 'exists:leads,id',
            'user_id' => 'required|exists:users,id',
        ]);

        Lead::whereIn('id', $request->lead_ids)->update([
            'user_id' => $request->user_id,
        ]);

        return back()->with('success', count($request->lead_ids) . ' leads assigned to client.');
    }

    /**
     * Get lead analytics data (AJAX)
     */
    public function analytics(Request $request)
    {
        $startDate = $request->get('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $clientId = $request->get('client');

        $query = Lead::whereBetween('created_at', [$startDate, $endDate . ' 23:59:59']);

        if ($clientId) {
            $query->where('user_id', $clientId);
        }

        // Daily breakdown
        $dailyData = $query->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date')
            ->toArray();

        // Source breakdown
        $sourceData = Lead::whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
            ->when($clientId, fn($q) => $q->where('user_id', $clientId))
            ->selectRaw('source, COUNT(*) as count')
            ->groupBy('source')
            ->pluck('count', 'source')
            ->toArray();

        // Status breakdown
        $statusData = Lead::whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
            ->when($clientId, fn($q) => $q->where('user_id', $clientId))
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return response()->json([
            'daily' => $dailyData,
            'source' => $sourceData,
            'status' => $statusData,
        ]);
    }
}