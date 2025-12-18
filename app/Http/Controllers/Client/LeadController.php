<?php
// app/Http/Controllers/Client/LeadController.php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\LeadRequest;
use App\Models\Lead;
use App\Services\LeadService;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    protected LeadService $leadService;

    public function __construct(LeadService $leadService)
    {
        $this->middleware(['auth', 'onboarding']);
        $this->leadService = $leadService;
    }

    /**
     * List all leads
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = Lead::where('user_id', $user->id);

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

        $leads = $query->latest()->paginate(20);

        // Get stats
        $stats = $this->leadService->getStats($user);

        return view('client.leads.index', compact('leads', 'stats'));
    }

    /**
     * Show lead details
     */
    public function show(Lead $lead)
    {
        $this->authorize('view', $lead);

        return view('client.leads.show', compact('lead'));
    }

    /**
     * Create manual lead
     */
    public function create()
    {
        return view('client.leads.create');
    }

    /**
     * Store manual lead
     */
    public function store(LeadRequest $request)
    {
        $user = auth()->user();

        $lead = $this->leadService->create($user, $request->validated());

        return redirect()->route('client.leads.show', $lead)
            ->with('success', 'Lead created successfully!');
    }

    /**
     * Update lead status
     */
    public function updateStatus(Request $request, Lead $lead)
    {
        $this->authorize('update', $lead);

        $request->validate([
            'status' => 'required|in:new,contacted,qualified,converted,lost,spam',
        ]);

        $this->leadService->updateStatus($lead, $request->status);

        return back()->with('success', 'Lead status updated successfully!');
    }

    /**
     * Update lead quality
     */
    public function updateQuality(Request $request, Lead $lead)
    {
        $this->authorize('update', $lead);

        $request->validate([
            'quality' => 'required|in:hot,warm,cold',
        ]);

        $lead->setQuality($request->quality);

        return back()->with('success', 'Lead quality updated successfully!');
    }

    /**
     * Add note to lead
     */
    public function addNote(Request $request, Lead $lead)
    {
        $this->authorize('update', $lead);

        $request->validate([
            'note' => 'required|string|max:2000',
        ]);

        $existingNotes = $lead->notes ?? '';
        $newNote = '[' . now()->format('Y-m-d H:i') . '] ' . $request->note;
        
        $lead->update([
            'notes' => $existingNotes ? $existingNotes . "\n\n" . $newNote : $newNote,
        ]);

        return back()->with('success', 'Note added successfully!');
    }

    /**
     * Export leads to CSV
     */
    public function export(Request $request)
    {
        $user = auth()->user();

        $csv = $this->leadService->exportToCsv($user, $request->all());

        $filename = 'leads_' . now()->format('Y-m-d_His') . '.csv';

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename={$filename}");
    }
}