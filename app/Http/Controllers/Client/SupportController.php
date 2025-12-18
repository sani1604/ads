<?php
// app/Http/Controllers/Client/SupportController.php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\SupportTicketRequest;
use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SupportController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'onboarding']);
    }

    /**
     * List all tickets
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = $user->supportTickets()->with('assignee');

        if ($request->filled('status')) {
            if ($request->status === 'open') {
                $query->open();
            } else {
                $query->closed();
            }
        }

        $tickets = $query->latest()->paginate(15);

        $stats = [
            'total' => $user->supportTickets()->count(),
            'open' => $user->supportTickets()->open()->count(),
            'resolved' => $user->supportTickets()->where('status', 'resolved')->count(),
        ];

        return view('client.support.index', compact('tickets', 'stats'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        return view('client.support.create');
    }

    /**
     * Store new ticket
     */
    public function store(SupportTicketRequest $request)
    {
        $user = auth()->user();

        $ticket = SupportTicket::create([
            'user_id' => $user->id,
            'ticket_number' => SupportTicket::generateTicketNumber(),
            'subject' => $request->subject,
            'category' => $request->category,
            'priority' => $request->priority ?? 'medium',
            'status' => 'open',
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
                ];
            }
        }

        // Create first message
        SupportTicketMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'message' => $request->message,
            'attachments' => !empty($attachments) ? $attachments : null,
        ]);

        return redirect()->route('client.support.show', $ticket)
            ->with('success', 'Support ticket created successfully. We will respond shortly.');
    }

    /**
     * Show ticket details
     */
    public function show(SupportTicket $ticket)
    {
        $this->authorize('view', $ticket);

        $ticket->load(['messages.user', 'assignee']);

        return view('client.support.show', compact('ticket'));
    }

    /**
     * Reply to ticket
     */
    public function reply(Request $request, SupportTicket $ticket)
    {
        $this->authorize('update', $ticket);

        $request->validate([
            'message' => 'required|string|max:5000',
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'file|mimes:jpg,jpeg,png,pdf,doc,docx|max:5120',
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
                ];
            }
        }

        SupportTicketMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'message' => $request->message,
            'attachments' => !empty($attachments) ? $attachments : null,
        ]);

        // Update ticket status
        if ($ticket->status === 'waiting_reply') {
            $ticket->update(['status' => 'open']);
        }

        return back()->with('success', 'Reply sent successfully.');
    }

    /**
     * Close ticket
     */
    public function close(SupportTicket $ticket)
    {
        $this->authorize('update', $ticket);

        $ticket->close();

        return back()->with('success', 'Ticket closed successfully.');
    }

    /**
     * Reopen ticket
     */
    public function reopen(SupportTicket $ticket)
    {
        $this->authorize('update', $ticket);

        $ticket->reopen();

        return back()->with('success', 'Ticket reopened successfully.');
    }
}