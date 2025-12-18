{{-- resources/views/client/support/show.blade.php --}}
@extends('layouts.client')

@section('title', $ticket->ticket_number)
@section('page-title', 'Support Ticket')

@section('content')
    <div class="row g-4">
        {{-- Conversation --}}
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1">{{ $ticket->subject }}</h5>
                        <small class="text-muted">Ticket #{{ $ticket->ticket_number }}</small>
                    </div>
                    <div class="text-end">
                        {!! $ticket->status_badge !!}
                        {!! $ticket->priority_badge !!}
                    </div>
                </div>
                <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                    @foreach($ticket->messages as $message)
                        <div class="mb-4">
                            <div class="d-flex">
                                <img src="{{ $message->user->avatar_url }}" class="rounded-circle me-3" width="36" height="36" alt="">
                                <div>
                                    <div class="d-flex align-items-center mb-1">
                                        <strong>{{ $message->user->name }}</strong>
                                        <small class="text-muted ms-2">{{ $message->created_at->diffForHumans() }}</small>
                                        @if($message->is_internal_note)
                                            <span class="badge bg-secondary ms-2">Internal</span>
                                        @endif
                                    </div>
                                    <p class="mb-1">{!! nl2br(e($message->message)) !!}</p>

                                    @if($message->attachments)
                                        <div class="mt-2">
                                            @foreach($message->attachments as $idx => $file)
                                                <a href="{{ route('client.support.show', $ticket) }}/messages/{{ $message->id }}/download/{{ $idx }}" 
                                                   class="badge bg-light text-dark text-decoration-none me-1 mb-1">
                                                    <i class="fas fa-paperclip me-1"></i>{{ $file['name'] }}
                                                </a>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @if(!$loop->last)
                            <hr>
                        @endif
                    @endforeach
                </div>
            </div>

            {{-- Reply box (if not closed) --}}
            @if(!in_array($ticket->status, ['closed']))
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Add Reply</h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('client.support.reply', $ticket) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <textarea name="message" class="form-control" rows="4" required placeholder="Type your reply here..."></textarea>
                            </div>
                            <x-form.file 
                                name="attachments[]"
                                label="Attachments (optional)"
                                :multiple="true"
                                accept=".jpg,.jpeg,.png,.pdf,.doc,.docx"
                            />
                            <div class="d-flex justify-content-between mt-3">
                                <div>
                                    @if($ticket->status === 'resolved')
                                        <button formaction="{{ route('client.support.reopen', $ticket) }}"
                                                formmethod="POST"
                                                class="btn btn-outline-secondary me-2">
                                            @csrf
                                            <i class="fas fa-undo me-1"></i> Reopen Ticket
                                        </button>
                                    @endif
                                </div>
                                <div class="d-flex gap-2">
                                    @if($ticket->status !== 'closed')
                                        <form action="{{ route('client.support.close', $ticket) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-danger">
                                                <i class="fas fa-check-circle me-1"></i> Mark Resolved
                                            </button>
                                        </form>
                                    @endif
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-paper-plane me-1"></i> Send Reply
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            @else
                <p class="text-muted">This ticket is closed. If you still need help, please open a new ticket.</p>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Ticket Info</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td class="text-muted">Ticket #</td>
                            <td class="text-end">{{ $ticket->ticket_number }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Category</td>
                            <td class="text-end">{{ $ticket->category_label }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Priority</td>
                            <td class="text-end">{!! $ticket->priority_badge !!}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Status</td>
                            <td class="text-end">{!! $ticket->status_badge !!}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Created</td>
                            <td class="text-end">{{ $ticket->created_at->format('M d, Y') }}</td>
                        </tr>
                        @if($ticket->resolved_at)
                            <tr>
                                <td class="text-muted">Resolved</td>
                                <td class="text-end">{{ $ticket->resolved_at->format('M d, Y') }}</td>
                            </tr>
                        @endif
                    </table>
                </div>
            </div>

            {{-- Back --}}
            <a href="{{ route('client.support.index') }}" class="btn btn-outline-secondary w-100">
                <i class="fas fa-arrow-left me-1"></i> Back to Tickets
            </a>
        </div>
    </div>
@endsection