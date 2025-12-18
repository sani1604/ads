<?php
// app/Http/Controllers/Client/CreativeController.php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreativeCommentRequest;
use App\Http\Requests\CreativeRequest;
use App\Models\Creative;
use App\Models\CreativeComment;
use App\Models\ServiceCategory;
use App\Services\CreativeService;
use Illuminate\Http\Request;

class CreativeController extends Controller
{
    protected CreativeService $creativeService;

    public function __construct(CreativeService $creativeService)
    {
        $this->middleware(['auth', 'onboarding']);
        $this->creativeService = $creativeService;
    }

    /**
     * List all creatives
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = Creative::where('user_id', $user->id)
            ->with(['files', 'serviceCategory'])
            ->latestVersions();

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by platform
        if ($request->filled('platform')) {
            $query->where('platform', $request->platform);
        }

        // Search
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $creatives = $query->latest()->paginate(12);

        $stats = [
            'total' => Creative::where('user_id', $user->id)->latestVersions()->count(),
            'pending' => Creative::where('user_id', $user->id)->latestVersions()->pendingApproval()->count(),
            'approved' => Creative::where('user_id', $user->id)->latestVersions()->approved()->count(),
            'changes_requested' => Creative::where('user_id', $user->id)->latestVersions()->changesRequested()->count(),
        ];

        return view('client.creatives.index', compact('creatives', 'stats'));
    }

    /**
     * Show creative details
     */
    public function show(Creative $creative)
    {
        $this->authorize('view', $creative);

        $creative->load(['files', 'comments.user', 'comments.replies.user', 'serviceCategory', 'versions']);

        return view('client.creatives.show', compact('creative'));
    }

    /**
     * Show create form (Optional: if client can upload)
     */
    public function create()
    {
        $user = auth()->user();

        // Check creative limits
        $subscription = $user->activeSubscription;
        if ($subscription) {
            $remaining = $subscription->getCreativesRemainingThisMonth();
            if ($remaining <= 0) {
                return redirect()->route('client.creatives.index')
                    ->with('error', 'You have reached your creative limit for this month.');
            }
        }

        $serviceCategories = ServiceCategory::active()->ordered()->get();

        return view('client.creatives.create', compact('serviceCategories'));
    }

    /**
     * Store new creative
     */
    public function store(CreativeRequest $request)
    {
        $user = auth()->user();

        $creative = $this->creativeService->create(
            $user,
            $request->validated(),
            $request->file('files')
        );

        return redirect()->route('client.creatives.show', $creative)
            ->with('success', 'Creative uploaded successfully!');
    }

    /**
     * Approve creative (Client approval)
     */
    public function approve(Creative $creative)
    {
        $this->authorize('update', $creative);

        if ($creative->status !== 'pending_approval') {
            return back()->with('error', 'This creative cannot be approved.');
        }

        $creative->approve(auth()->user());

        return back()->with('success', 'Creative approved successfully!');
    }

    /**
     * Request changes
     */
    public function requestChanges(Request $request, Creative $creative)
    {
        $this->authorize('update', $creative);

        $request->validate([
            'feedback' => 'required|string|max:2000',
        ]);

        // Add comment with feedback
        CreativeComment::create([
            'creative_id' => $creative->id,
            'user_id' => auth()->id(),
            'comment' => $request->feedback,
        ]);

        $this->creativeService->requestChanges($creative, auth()->user());

        return back()->with('success', 'Change request submitted successfully!');
    }

    /**
     * Add comment to creative
     */
    public function addComment(CreativeCommentRequest $request, Creative $creative)
    {
        $this->authorize('view', $creative);

        $comment = CreativeComment::create([
            'creative_id' => $creative->id,
            'user_id' => auth()->id(),
            'parent_id' => $request->parent_id,
            'comment' => $request->comment,
            'position' => $request->position,
        ]);

        return back()->with('success', 'Comment added successfully!');
    }

    /**
     * Resolve comment
     */
    public function resolveComment(CreativeComment $comment)
    {
        $this->authorize('update', $comment->creative);

        $comment->resolve();

        return back()->with('success', 'Comment marked as resolved.');
    }

    /**
     * Download creative file
     */
    public function download(Creative $creative)
    {
        $this->authorize('view', $creative);

        $file = $creative->primary_file;

        if (!$file) {
            return back()->with('error', 'No file found.');
        }

        return response()->download(
            storage_path('app/public/' . $file->file_path),
            $file->original_name
        );
    }
}