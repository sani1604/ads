<?php
// app/Http/Controllers/Admin/SupportTicketController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use App\Models\User;
use App\Services\NotificationService;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SupportTicketController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin,manager']);
    }

    /**
     * List all tickets
     */
    public function index(Request $request)
    {
        $query = SupportTicket::with(['user', 'assignee', 'messages' => fn($q) => $q->latest()->first()]);

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'open') {
                $query->open();
            } elseif ($request->status === 'closed') {
                $query->closed();
            } else {
                $query->where('status', $request->status);
            }
        }

        // Filter by priority
        if ($request->filled('priority')) {
            $query->byPriority($request->priority);
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->byCategory($request->category);
        }

        // Filter by client
        if ($request->filled('client')) {
            $query->where('user_id', $request->client);
        }

        // Filter by assignee
        if ($request->filled('assignee')) {
            if ($request->assignee === 'unassigned') {
                $query->whereNull('assigned_to');
            } else {
                $query->where('assigned_to', $request->assignee);
            }
        }

        // Search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('ticket_number', 'like', '%' . $request->search . '%')
                    ->orWhere('subject', 'like', '%' . $request->search . '%')
                    ->orWhereHas('user', fn($q2) => $q2->search($request->search));
            });
        }

        // Date range
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59',
            ]);
        }

        // Sorting
        $sortBy = $request->get('sort', 'created_at');
        $sortOrder = $request->get('order', 'desc');

        // Priority sorting (urgent first)
        if ($sortBy === 'priority') {
            $query->orderByRaw("FIELD(priority, 'urgent', 'high', 'medium', 'low') " . $sortOrder);
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        $tickets = $query->paginate(20)->withQueryString();

        // Filter options
        $clients = User::clients()->orderBy('name')->get(['id', 'name', 'company_name']);
        $staff = User::admins()->orderBy('name')->get(['id', 'name']);

        // Stats
        $stats = [
            'total' => SupportTicket::count(),
            'open' => SupportTicket::open()->count(),
            'in_progress' => SupportTicket::where('status', 'in_progress')->count(),
            'waiting_reply' => SupportTicket::where('status', 'waiting_reply')->count(),
            'resolved' => SupportTicket::where('status', 'resolved')->count(),
            'urgent' => SupportTicket::open()->byPriority('urgent')->count(),
            'unassigned' => SupportTicket::open()->whereNull('assigned_to')->count(),
        ];

        // Category breakdown
        $categoryBreakdown = SupportTicket::open()
            ->selectRaw('category, COUNT(*) as count')
            ->groupBy('category')
            ->pluck('count', 'category')
            ->toArray();

        return view('admin.support-tickets.index', compact(
            'tickets',
            'clients',
            'staff',
            'stats',
            'categoryBreakdown'
        ));
    }

    /**
     * Show ticket details
     */
    public function show(SupportTicket $supportTicket)
    {
        $supportTicket->load([
            'user',
            'assignee',
            'messages' => fn($q) => $q->with('user')->orderBy('created_at', 'asc'),
        ]);

        // Get other tickets from same client
        $relatedTickets = SupportTicket::where('user_id', $supportTicket->user_id)
            ->where('id', '!=', $supportTicket->id)
            ->latest()
            ->take(5)
            ->get();

        // Staff for assignment
        $staff = User::admins()->active()->orderBy('name')->get(['id', 'name']);

        return view('admin.support-tickets.show', compact('supportTicket', 'relatedTickets', 'staff'));
    }

    /**
     * Reply to ticket
     */
    public function reply(Request $request, SupportTicket $supportTicket)
    {
        $request->validate([
            'message' => 'required|string|max:10000',
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'file|mimes:jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx,zip|max:10240',
            'is_internal_note' => 'boolean',
            'change_status' => 'nullable|in:open,in_progress,waiting_reply,resolved,closed',
        ]);

        // Handle attachments
        $attachments = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store("tickets/{$supportTicket->id}", 'public');
                $attachments[] = [
                    'path' => $path,
                    'name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'type' => $file->getClientMimeType(),
                ];
            }
        }

        // Create message
        $message = SupportTicketMessage::create([
            'ticket_id' => $supportTicket->id,
            'user_id' => auth()->id(),
            'message' => $request->message,
            'attachments' => !empty($attachments) ? $attachments : null,
            'is_internal_note' => $request->boolean('is_internal_note'),
        ]);

        // Update ticket status
        $newStatus = $request->change_status;
        if (!$newStatus && !$request->boolean('is_internal_note')) {
            $newStatus = 'waiting_reply';
        }

        if ($newStatus) {
            $supportTicket->update([
                'status' => $newStatus,
                'resolved_at' => $newStatus === 'resolved' ? now() : $supportTicket->resolved_at,
            ]);
        }

        // Auto-assign if not assigned
        if (!$supportTicket->assigned_to) {
            $supportTicket->update(['assigned_to' => auth()->id()]);
        }

        // Notify client (if not internal note)
        if (!$request->boolean('is_internal_note')) {
            NotificationService::ticketReply($supportTicket->user, $supportTicket);

            // Send email notification
            $this->sendTicketReplyEmail($supportTicket, $message);
        }

        ActivityLogService::log(
            'ticket_replied',
            "Replied to ticket #{$supportTicket->ticket_number}",
            $supportTicket,
            ['is_internal' => $request->boolean('is_internal_note')]
        );

        return back()->with('success', 'Reply sent successfully.');
    }

    /**
     * Update ticket status
     */
    public function updateStatus(Request $request, SupportTicket $supportTicket)
    {
        $request->validate([
            'status' => 'required|in:open,in_progress,waiting_reply,resolved,closed',
        ]);

        $oldStatus = $supportTicket->status;
        $newStatus = $request->status;

        $updateData = ['status' => $newStatus];

        if ($newStatus === 'resolved' && $oldStatus !== 'resolved') {
            $updateData['resolved_at'] = now();
        }

        $supportTicket->update($updateData);

        // Notify client of status change
        if ($oldStatus !== $newStatus) {
            $this->notifyStatusChange($supportTicket, $oldStatus, $newStatus);
        }

        ActivityLogService::log(
            'ticket_status_changed',
            "Ticket status changed from {$oldStatus} to {$newStatus}",
            $supportTicket
        );

        return back()->with('success', 'Ticket status updated.');
    }

    /**
     * Update ticket priority
     */
    public function updatePriority(Request $request, SupportTicket $supportTicket)
    {
        $request->validate([
            'priority' => 'required|in:low,medium,high,urgent',
        ]);

        $supportTicket->update(['priority' => $request->priority]);

        return back()->with('success', 'Ticket priority updated.');
    }

    /**
     * Assign ticket to staff
     */
    public function assign(Request $request, SupportTicket $supportTicket)
    {
        $request->validate([
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $assignee = $request->assigned_to ? User::find($request->assigned_to) : null;

        $supportTicket->update([
            'assigned_to' => $request->assigned_to,
            'status' => $supportTicket->status === 'open' ? 'in_progress' : $supportTicket->status,
        ]);

        ActivityLogService::log(
            'ticket_assigned',
            "Ticket assigned to " . ($assignee ? $assignee->name : 'Unassigned'),
            $supportTicket
        );

        return back()->with('success', 'Ticket assignment updated.');
    }

    /**
     * Assign ticket to self
     */
    public function assignToSelf(SupportTicket $supportTicket)
    {
        $supportTicket->update([
            'assigned_to' => auth()->id(),
            'status' => $supportTicket->status === 'open' ? 'in_progress' : $supportTicket->status,
        ]);

        return back()->with('success', 'Ticket assigned to you.');
    }

    /**
     * Bulk assign tickets
     */
    public function bulkAssign(Request $request)
    {
        $request->validate([
            'ticket_ids' => 'required|array|min:1',
            'ticket_ids.*' => 'exists:support_tickets,id',
            'assigned_to' => 'required|exists:users,id',
        ]);

        SupportTicket::whereIn('id', $request->ticket_ids)->update([
            'assigned_to' => $request->assigned_to,
        ]);

        return back()->with('success', count($request->ticket_ids) . ' tickets assigned.');
    }

    /**
     * Bulk update status
     */
    public function bulkUpdateStatus(Request $request)
    {
        $request->validate([
            'ticket_ids' => 'required|array|min:1',
            'ticket_ids.*' => 'exists:support_tickets,id',
            'status' => 'required|in:open,in_progress,waiting_reply,resolved,closed',
        ]);

        $updateData = ['status' => $request->status];

        if ($request->status === 'resolved') {
            $updateData['resolved_at'] = now();
        }

        SupportTicket::whereIn('id', $request->ticket_ids)->update($updateData);

        return back()->with('success', count($request->ticket_ids) . ' tickets updated.');
    }

    /**
     * Resolve ticket
     */
    public function resolve(Request $request, SupportTicket $supportTicket)
    {
        $request->validate([
            'resolution_message' => 'nullable|string|max:2000',
        ]);

        // Add resolution message if provided
        if ($request->filled('resolution_message')) {
            SupportTicketMessage::create([
                'ticket_id' => $supportTicket->id,
                'user_id' => auth()->id(),
                'message' => "**Resolution:** " . $request->resolution_message,
            ]);
        }

        $supportTicket->resolve();

        // Notify client
        NotificationService::send(
            $supportTicket->user,
            'ticket_resolved',
            'Support Ticket Resolved',
            "Your support ticket #{$supportTicket->ticket_number} has been resolved.",
            route('client.support.show', $supportTicket),
            ['ticket_id' => $supportTicket->id]
        );

        return back()->with('success', 'Ticket resolved successfully.');
    }

    /**
     * Reopen ticket
     */
    public function reopen(SupportTicket $supportTicket)
    {
        $supportTicket->reopen();

        ActivityLogService::log(
            'ticket_reopened',
            "Ticket #{$supportTicket->ticket_number} reopened",
            $supportTicket
        );

        return back()->with('success', 'Ticket reopened.');
    }

    /**
     * Close ticket
     */
    public function close(SupportTicket $supportTicket)
    {
        $supportTicket->close();

        return back()->with('success', 'Ticket closed.');
    }

    /**
     * Delete ticket
     */
    public function destroy(SupportTicket $supportTicket)
    {
        // Delete attachments
        foreach ($supportTicket->messages as $message) {
            if ($message->attachments) {
                foreach ($message->attachments as $attachment) {
                    if (Storage::disk('public')->exists($attachment['path'])) {
                        Storage::disk('public')->delete($attachment['path']);
                    }
                }
            }
        }

        $ticketNumber = $supportTicket->ticket_number;
        $supportTicket->delete();

        ActivityLogService::log(
            'ticket_deleted',
            "Ticket #{$ticketNumber} deleted",
            null
        );

        return redirect()->route('admin.support-tickets.index')
            ->with('success', 'Ticket deleted.');
    }

    /**
     * Delete message
     */
    public function deleteMessage(SupportTicketMessage $message)
    {
        // Delete attachments
        if ($message->attachments) {
            foreach ($message->attachments as $attachment) {
                if (Storage::disk('public')->exists($attachment['path'])) {
                    Storage::disk('public')->delete($attachment['path']);
                }
            }
        }

        $message->delete();

        return back()->with('success', 'Message deleted.');
    }

    /**
     * Download attachment
     */
    public function downloadAttachment(SupportTicket $supportTicket, $messageId, $index)
    {
        $message = $supportTicket->messages()->findOrFail($messageId);

        if (!$message->attachments || !isset($message->attachments[$index])) {
            abort(404, 'Attachment not found.');
        }

        $attachment = $message->attachments[$index];
        $path = storage_path('app/public/' . $attachment['path']);

        if (!file_exists($path)) {
            abort(404, 'File not found.');
        }

        return response()->download($path, $attachment['name']);
    }

    /**
     * Create ticket on behalf of client
     */
    public function create(Request $request)
    {
        $clients = User::clients()->active()->orderBy('name')->get();
        $selectedClient = $request->filled('client') ? User::find($request->client) : null;

        return view('admin.support-tickets.create', compact('clients', 'selectedClient'));
    }

    /**
     * Store ticket on behalf of client
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'subject' => 'required|string|max:255',
            'category' => 'required|in:billing,technical,creative,leads,general',
            'priority' => 'required|in:low,medium,high,urgent',
            'message' => 'required|string|max:10000',
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'file|mimes:jpg,jpeg,png,gif,pdf,doc,docx|max:10240',
        ]);

        $client = User::findOrFail($request->user_id);

        // Create ticket
        $ticket = SupportTicket::create([
            'user_id' => $client->id,
            'ticket_number' => SupportTicket::generateTicketNumber(),
            'subject' => $request->subject,
            'category' => $request->category,
            'priority' => $request->priority,
            'status' => 'open',
            'assigned_to' => auth()->id(),
        ]);

        // Handle attachments
        $attachments = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store("tickets/{$ticket->id}", 'public');
                $attachments[] = [
                    'path' => $path,
                    'name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'type' => $file->getClientMimeType(),
                ];
            }
        }

        // Create initial message
        SupportTicketMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'message' => $request->message,
            'attachments' => !empty($attachments) ? $attachments : null,
        ]);

        // Notify client
        NotificationService::send(
            $client,
            'ticket_created',
            'Support Ticket Created',
            "A support ticket has been created on your behalf: #{$ticket->ticket_number}",
            route('client.support.show', $ticket),
            ['ticket_id' => $ticket->id]
        );

        return redirect()->route('admin.support-tickets.show', $ticket)
            ->with('success', 'Ticket created successfully.');
    }

    /**
     * Merge tickets
     */
    public function merge(Request $request)
    {
        $request->validate([
            'primary_ticket_id' => 'required|exists:support_tickets,id',
            'merge_ticket_ids' => 'required|array|min:1',
            'merge_ticket_ids.*' => 'exists:support_tickets,id',
        ]);

        $primaryTicket = SupportTicket::findOrFail($request->primary_ticket_id);

        foreach ($request->merge_ticket_ids as $ticketId) {
            if ($ticketId == $primaryTicket->id) {
                continue;
            }

            $ticket = SupportTicket::with('messages')->find($ticketId);

            if ($ticket) {
                // Move messages to primary ticket
                foreach ($ticket->messages as $message) {
                    $message->update(['ticket_id' => $primaryTicket->id]);
                }

                // Add merge note
                SupportTicketMessage::create([
                    'ticket_id' => $primaryTicket->id,
                    'user_id' => auth()->id(),
                    'message' => "**Merged from ticket #{$ticket->ticket_number}**",
                    'is_internal_note' => true,
                ]);

                // Delete merged ticket
                $ticket->delete();
            }
        }

        return redirect()->route('admin.support-tickets.show', $primaryTicket)
            ->with('success', 'Tickets merged successfully.');
    }

    /**
     * Export tickets
     */
    public function export(Request $request)
    {
        $query = SupportTicket::with(['user', 'assignee']);

        if ($request->filled('status')) {
            if ($request->status === 'open') {
                $query->open();
            } elseif ($request->status === 'closed') {
                $query->closed();
            } else {
                $query->where('status', $request->status);
            }
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59',
            ]);
        }

        $tickets = $query->latest()->get();

        $csv = "Ticket Number,Client,Subject,Category,Priority,Status,Assignee,Created At,Resolved At\n";

        foreach ($tickets as $ticket) {
            $csv .= implode(',', [
                $ticket->ticket_number,
                '"' . ($ticket->user->company_name ?? $ticket->user->name) . '"',
                '"' . str_replace('"', '""', $ticket->subject) . '"',
                $ticket->category,
                $ticket->priority,
                $ticket->status,
                '"' . ($ticket->assignee?->name ?? 'Unassigned') . '"',
                $ticket->created_at->format('Y-m-d H:i:s'),
                $ticket->resolved_at ? $ticket->resolved_at->format('Y-m-d H:i:s') : '',
            ]) . "\n";
        }

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename=support_tickets_' . now()->format('Y-m-d') . '.csv');
    }

    /**
     * Get ticket statistics (AJAX)
     */
    public function statistics(Request $request)
    {
        $startDate = $request->get('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        // Tickets over time
        $ticketsOverTime = SupportTicket::whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date')
            ->toArray();

        // Average resolution time
        $avgResolutionTime = SupportTicket::whereNotNull('resolved_at')
            ->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, resolved_at)) as avg_hours')
            ->value('avg_hours') ?? 0;

        // By category
        $byCategory = SupportTicket::whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
            ->selectRaw('category, COUNT(*) as count')
            ->groupBy('category')
            ->pluck('count', 'category')
            ->toArray();

        // By priority
        $byPriority = SupportTicket::whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
            ->selectRaw('priority, COUNT(*) as count')
            ->groupBy('priority')
            ->pluck('count', 'priority')
            ->toArray();

        // By staff (resolved)
        $byStaff = SupportTicket::whereNotNull('assigned_to')
            ->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
            ->with('assignee:id,name')
            ->selectRaw('assigned_to, COUNT(*) as total, SUM(CASE WHEN status = "resolved" THEN 1 ELSE 0 END) as resolved')
            ->groupBy('assigned_to')
            ->get();

        return response()->json([
            'tickets_over_time' => $ticketsOverTime,
            'avg_resolution_hours' => round($avgResolutionTime, 1),
            'by_category' => $byCategory,
            'by_priority' => $byPriority,
            'by_staff' => $byStaff,
        ]);
    }

    /**
     * Send ticket reply email
     */
    protected function sendTicketReplyEmail(SupportTicket $ticket, SupportTicketMessage $message): void
    {
        try {
            \Mail::send('emails.ticket-reply', [
                'ticket' => $ticket,
                'message' => $message,
            ], function ($mail) use ($ticket) {
                $mail->to($ticket->user->email, $ticket->user->name)
                    ->subject("Re: [{$ticket->ticket_number}] {$ticket->subject}");
            });
        } catch (\Exception $e) {
            \Log::error('Failed to send ticket reply email: ' . $e->getMessage());
        }
    }

    /**
     * Notify client of status change
     */
    protected function notifyStatusChange(SupportTicket $ticket, string $oldStatus, string $newStatus): void
    {
        $statusLabels = [
            'open' => 'Open',
            'in_progress' => 'In Progress',
            'waiting_reply' => 'Awaiting Your Reply',
            'resolved' => 'Resolved',
            'closed' => 'Closed',
        ];

        NotificationService::send(
            $ticket->user,
            'ticket_status_changed',
            'Ticket Status Updated',
            "Your ticket #{$ticket->ticket_number} status changed to: {$statusLabels[$newStatus]}",
            route('client.support.show', $ticket),
            ['ticket_id' => $ticket->id, 'new_status' => $newStatus]
        );
    }
}