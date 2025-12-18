<?php
// app/Http/Controllers/Api/LeadController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Services\LeadService;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    protected LeadService $leadService;

    public function __construct(LeadService $leadService)
    {
        $this->leadService = $leadService;
    }

    public function index(Request $request)
    {
        $user = $request->user();

        $query = Lead::where('user_id', $user->id);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        $leads = $query->latest()->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $leads,
        ]);
    }

    public function show(Request $request, Lead $lead)
    {
        if ($lead->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $lead,
        ]);
    }

    public function updateStatus(Request $request, Lead $lead)
    {
        if ($lead->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        $request->validate([
            'status' => 'required|in:new,contacted,qualified,converted,lost,spam',
        ]);

        $this->leadService->updateStatus($lead, $request->status);

        return response()->json([
            'success' => true,
            'message' => 'Lead status updated.',
            'data' => $lead->fresh(),
        ]);
    }
}