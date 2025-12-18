{{-- resources/views/emails/ticket-reply.blade.php --}}
@extends('emails.layouts.base')

@section('subject', 'New Reply on Ticket ' . $ticket->ticket_number)

@section('content')
    <p style="font-size:14px;color:#111827;margin:0 0 12px;">
        Hi {{ $ticket->user->name }},
    </p>

    <p style="font-size:14px;color:#111827;margin:0 0 12px;">
        There is a new reply on your support ticket <strong>#{{ $ticket->ticket_number }}</strong>:
    </p>

    <p style="font-size:14px;color:#111827;margin:0 0 12px;">
        <strong>Subject:</strong> {{ $ticket->subject }}
    </p>

    <div style="border-left:3px solid #e5e7eb;padding-left:10px;margin:0 0 16px;">
        <p style="font-size:13px;color:#374151;margin:0 0 4px;">
            <strong>{{ $message->user->name }}</strong> wrote:
        </p>
        <p style="font-size:13px;color:#111827;margin:0 0 4px;white-space:pre-line;">
            {{ $message->message }}
        </p>
    </div>

    <p style="font-size:14px;color:#111827;margin:0 0 16px;">
        Please log in to your client portal to view the full conversation and reply:
    </p>

    <p style="margin:0 0 16px;">
        <a href="{{ route('client.support.show', $ticket) }}"
           style="display:inline-block;padding:10px 18px;background:#6366f1;color:#ffffff;text-decoration:none;border-radius:4px;font-size:14px;">
            View Ticket
        </a>
    </p>

    <p style="font-size:13px;color:#6b7280;margin:0;">
        If the button above does not work, copy and paste this URL into your browser:<br>
        <span style="word-break:break-all;">{{ route('client.support.show', $ticket) }}</span>
    </p>
@endsection