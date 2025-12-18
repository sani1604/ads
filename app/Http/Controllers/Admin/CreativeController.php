<?php
// app/Http/Controllers/Admin/CreativeController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreativeRequest;
use App\Models\Creative;
use App\Models\CreativeComment;
use App\Models\ServiceCategory;
use App\Models\User;
use App\Services\CreativeService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CreativeController extends Controller
{
    protected CreativeService $creativeService;

    public function __construct(CreativeService $creativeService)
    {
        $this->middleware(['auth', 'role:admin,manager']);
        $this->creativeService = $creativeService;
    }

    /**
     * List all creatives
     */
    public function index(Request $request)
    {
        $query = Creative::with(['user', 'files', 'serviceCategory'])
            ->latestVersions();

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by client
        if ($request->filled('client')) {
            $query->where('user_id', $request->client);
        }

        // Filter by platform
        if ($request->filled('platform')) {
            $query->where('platform', $request->platform);
        }

        // Filter by service category
        if ($request->filled('category')) {
            $query->where('service_category_id', $request->category);
        }

        // Search
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        // Date range
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [$request->start_date, $request->end_date . ' 23:59:59']);
        }

        $creatives = $query->latest()->paginate(20)->withQueryString();

        // Get filter options
        $clients = User::clients()->active()->orderBy('name')->get(['id', 'name', 'company_name']);
        $categories = ServiceCategory::active()->ordered()->get();

        // Stats
        $stats = [
            'total' => Creative::latestVersions()->count(),
            'pending' => Creative::latestVersions()->pendingApproval()->count(),
            'approved' => Creative::latestVersions()->approved()->count(),
            'changes_requested' => Creative::latestVersions()->changesRequested()->count(),
        ];

        return view('admin.creatives.index', compact('creatives', 'clients', 'categories', 'stats'));
    }

    /**
     * Show creative details
     */
    public function show(Creative $creative)
    {
        $creative->load([
            'user',
            'files',
            'serviceCategory',
            'subscription.package',
            'comments' => fn($q) => $q->with(['user', 'replies.user'])->whereNull('parent_id'),
            'versions.files',
            'approver',
        ]);

        return view('admin.creatives.show', compact('creative'));
    }

    /**
     * Show create form (for uploading on behalf of client)
     */
    public function create(Request $request)
    {
        $clients = User::clients()->active()->whereHas('subscriptions', fn($q) => $q->active())->orderBy('name')->get();
        $categories = ServiceCategory::active()->ordered()->get();

        $selectedClient = $request->filled('client') ? User::find($request->client) : null;

        return view('admin.creatives.create', compact('clients', 'categories', 'selectedClient'));
    }

    /**
     * Store new creative (on behalf of client)
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'type' => 'required|in:image,video,carousel,story,reel,document',
            'platform' => 'required|in:facebook,instagram,google,linkedin,twitter,youtube,all',
            'service_category_id' => 'nullable|exists:service_categories,id',
            'ad_copy' => 'nullable|string|max:2000',
            'cta_text' => 'nullable|string|max:50',
            'landing_url' => 'nullable|url|max:500',
            'scheduled_date' => 'nullable|date',
            'files' => 'required|array|min:1|max:10',
            'files.*' => 'file|mimes:jpg,jpeg,png,gif,mp4,mov,avi,pdf|max:51200',
            'submit_for_approval' => 'boolean',
        ]);

        $client = User::findOrFail($request->user_id);

        $creative = $this->creativeService->create(
            $client,
            $request->only([
                'title', 'description', 'type', 'platform',
                'service_category_id', 'ad_copy', 'cta_text', 'landing_url', 'scheduled_date'
            ]),
            $request->file('files')
        );

        // Submit for approval if requested
        if ($request->boolean('submit_for_approval')) {
            $creative->submitForApproval();
        }

        return redirect()->route('admin.creatives.show', $creative)
            ->with('success', 'Creative uploaded successfully.');
    }

    /**
     * Show edit form
     */
    public function edit(Creative $creative)
    {
        $creative->load(['user', 'files', 'serviceCategory']);
        $categories = ServiceCategory::active()->ordered()->get();

        return view('admin.creatives.edit', compact('creative', 'categories'));
    }

    /**
     * Update creative
     */
    public function update(Request $request, Creative $creative)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'type' => 'required|in:image,video,carousel,story,reel,document',
            'platform' => 'required|in:facebook,instagram,google,linkedin,twitter,youtube,all',
            'service_category_id' => 'nullable|exists:service_categories,id',
            'ad_copy' => 'nullable|string|max:2000',
            'cta_text' => 'nullable|string|max:50',
            'landing_url' => 'nullable|url|max:500',
            'scheduled_date' => 'nullable|date',
            'files' => 'nullable|array|max:10',
            'files.*' => 'file|mimes:jpg,jpeg,png,gif,mp4,mov,avi,pdf|max:51200',
        ]);

        $files = $request->hasFile('files') ? $request->file('files') : null;

        $this->creativeService->update(
            $creative,
            $request->only([
                'title', 'description', 'type', 'platform',
                'service_category_id', 'ad_copy', 'cta_text', 'landing_url', 'scheduled_date'
            ]),
            $files
        );

        return redirect()->route('admin.creatives.show', $creative)
            ->with('success', 'Creative updated successfully.');
    }

    /**
     * Approve creative
     */
    public function approve(Creative $creative)
    {
        if (!$creative->isPending()) {
            return back()->with('error', 'This creative cannot be approved.');
        }

        $this->creativeService->approve($creative, auth()->user());

        return back()->with('success', 'Creative approved successfully.');
    }

    /**
     * Bulk approve creatives
     */
    public function bulkApprove(Request $request)
    {
        $request->validate([
            'creative_ids' => 'required|array|min:1',
            'creative_ids.*' => 'exists:creatives,id',
        ]);

        $count = 0;
        foreach ($request->creative_ids as $id) {
            $creative = Creative::find($id);
            if ($creative && $creative->isPending()) {
                $this->creativeService->approve($creative, auth()->user());
                $count++;
            }
        }

        return back()->with('success', "{$count} creatives approved successfully.");
    }

    /**
     * Request changes
     */
    public function requestChanges(Request $request, Creative $creative)
    {
        $request->validate([
            'feedback' => 'required|string|max:2000',
        ]);

        // Add feedback as comment
        CreativeComment::create([
            'creative_id' => $creative->id,
            'user_id' => auth()->id(),
            'comment' => $request->feedback,
        ]);

        $this->creativeService->requestChanges($creative, auth()->user());

        return back()->with('success', 'Change request sent to client.');
    }

    /**
     * Reject creative
     */
    public function reject(Request $request, Creative $creative)
    {
        $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        // Add rejection reason as comment
        CreativeComment::create([
            'creative_id' => $creative->id,
            'user_id' => auth()->id(),
            'comment' => 'Rejected: ' . $request->reason,
        ]);

        $creative->reject();

        // Notify client
        NotificationService::send(
            $creative->user,
            'creative_rejected',
            'Creative Rejected',
            "Your creative '{$creative->title}' has been rejected. Please check the feedback.",
            route('client.creatives.show', $creative),
            ['creative_id' => $creative->id]
        );

        return back()->with('success', 'Creative rejected.');
    }

    /**
     * Mark as published
     */
    public function markPublished(Creative $creative)
    {
        if ($creative->status !== 'approved') {
            return back()->with('error', 'Only approved creatives can be marked as published.');
        }

        $creative->update(['status' => 'published']);

        return back()->with('success', 'Creative marked as published.');
    }

    /**
     * Add comment
     */
    public function addComment(Request $request, Creative $creative)
    {
        $request->validate([
            'comment' => 'required|string|max:2000',
            'parent_id' => 'nullable|exists:creative_comments,id',
            'position' => 'nullable|array',
            'position.x' => 'required_with:position|numeric',
            'position.y' => 'required_with:position|numeric',
        ]);

        CreativeComment::create([
            'creative_id' => $creative->id,
            'user_id' => auth()->id(),
            'parent_id' => $request->parent_id,
            'comment' => $request->comment,
            'position' => $request->position,
        ]);

        return back()->with('success', 'Comment added.');
    }

    /**
     * Resolve comment
     */
    public function resolveComment(CreativeComment $comment)
    {
        $comment->resolve();

        return back()->with('success', 'Comment resolved.');
    }

    /**
     * Delete file
     */
    public function deleteFile(Creative $creative, $fileId)
    {
        $file = $creative->files()->findOrFail($fileId);

        // Ensure at least one file remains
        if ($creative->files()->count() <= 1) {
            return back()->with('error', 'Cannot delete the only file.');
        }

        $this->creativeService->deleteFile($file);

        return back()->with('success', 'File deleted.');
    }

    /**
     * Delete creative
     */
    public function destroy(Creative $creative)
    {
        // Delete all files
        foreach ($creative->files as $file) {
            if (Storage::disk('public')->exists($file->file_path)) {
                Storage::disk('public')->delete($file->file_path);
            }
        }

        $creative->delete();

        return redirect()->route('admin.creatives.index')
            ->with('success', 'Creative deleted successfully.');
    }

    /**
     * Bulk delete
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'creative_ids' => 'required|array|min:1',
            'creative_ids.*' => 'exists:creatives,id',
        ]);

        foreach ($request->creative_ids as $id) {
            $creative = Creative::with('files')->find($id);
            if ($creative) {
                foreach ($creative->files as $file) {
                    if (Storage::disk('public')->exists($file->file_path)) {
                        Storage::disk('public')->delete($file->file_path);
                    }
                }
                $creative->delete();
            }
        }

        return back()->with('success', count($request->creative_ids) . ' creatives deleted.');
    }

    /**
     * Download creative
     */
    public function download(Creative $creative)
    {
        $file = $creative->primary_file;

        if (!$file) {
            return back()->with('error', 'No file found.');
        }

        $path = storage_path('app/public/' . $file->file_path);

        if (!file_exists($path)) {
            return back()->with('error', 'File not found on server.');
        }

        return response()->download($path, $file->original_name);
    }

    /**
     * Download all files as ZIP
     */
    public function downloadAll(Creative $creative)
    {
        $files = $creative->files;

        if ($files->isEmpty()) {
            return back()->with('error', 'No files found.');
        }

        $zipFileName = 'creative_' . $creative->id . '_' . now()->format('Ymd_His') . '.zip';
        $zipPath = storage_path('app/temp/' . $zipFileName);

        // Ensure temp directory exists
        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        $zip = new \ZipArchive();
        $zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        foreach ($files as $file) {
            $filePath = storage_path('app/public/' . $file->file_path);
            if (file_exists($filePath)) {
                $zip->addFile($filePath, $file->original_name);
            }
        }

        $zip->close();

        return response()->download($zipPath, $zipFileName)->deleteFileAfterSend(true);
    }
}