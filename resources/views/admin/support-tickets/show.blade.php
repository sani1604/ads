{{-- resources/views/admin/support-tickets/show.blade.php --}}
@extends('layouts.admin')

@section('title', $supportTicket->ticket_number)

@section('content')
    <div class="row g-4">
        {{-- Conversation --}}
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1">{{ $supportTicket->subject }}</h5>
                        <small class="text-muted">
                            Ticket #{{ $supportTicket->ticket_number }} • 
                            {{ $supportTicket->user->company_name ?? $supportTicket->user->name }}
                        </small>
                    </div>
                    <div class="text-end">
                        {!! $supportTicket->status_badge !!}
                        {!! $supportTicket->priority_badge !!}
                    </div>
                </div>
                <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                    @foreach($supportTicket->messages as $msg)
                        <div class="mb-4">
                            <div class="d-flex">
                                <img src="{{ $msg->user->avatar_url }}" class="rounded-circle me-2" width="36" height="36">
                                <div>
                                    <div class="d-flex align-items-center mb-1">
                                        <strong>{{ $msg->user->name }}</strong>
                                        <small class="text-muted ms-2">{{ $msg->created_at->diffForHumans() }}</small>
                                        @if($msg->is_internal_note)
                                            <span class="badge bg-secondary ms-2">Internal</span>
                                        @endif
                                    </div>
                                    <p class="mb-1">{!! nl2br(e($msg->message)) !!}</p>
                                    @if($msg->attachments)
                                        <div class="mt-2">
                                            @foreach($msg->attachments as $i => $att)
                                                <a href="{{ route('admin.support-tickets.download-attachment', [$supportTicket, $msg->id, $i]) }}"
                                                   class="badge bg-light text-dark text-decoration-none me-1 mb-1">
                                                    <i class="fas fa-paperclip me-1"></i>{{ $att['name'] }}
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

            {{-- Reply / Internal note --}}
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Reply to Ticket</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.support-tickets.reply', $supportTicket) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <textarea name="message" class="form-control" rows="4" placeholder="Type your reply..." required></textarea>
                        </div>
                        <x-form.file 
                            name="attachments[]"
                            label="Attachments (optional)"
                            :multiple="true"
                            accept=".jpg,.jpeg,.png,.pdf,.doc,.docx"
                        />
                        <div class="form-check mt-2 mb-3">
                            <input class="form-check-input" type="checkbox" id="internal" name="is_internal_note" value="1">
                            <label class="form-check-label" for="internal">
                                Mark as internal note (client will not see this)
                            </label>
                        </div>
                        <div class="d-flex justify-content-between">
                            <div>
                                @if($supportTicket->status !== 'closed')
                                    <form action="{{ route('admin.support-tickets.close', $supportTicket) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button class="btn btn-outline-danger btn-sm">
                                            <i class="fas fa-check-circle me-1"></i>Mark Resolved
                                        </button>
                                    </form>
                                @endif
                            </div>
                            <button class="btn btn-primary">
                                <i class="fas fa-paper-plane me-1"></i>Send Reply
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Sidebar: Info & assignment --}}
        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">Ticket Info</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td class="text-muted">Category</td>
                            <td class="text-end">{{ $supportTicket->category_label }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Priority</td>
                            <td class="text-end">{!! $supportTicket->priority_badge !!}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Status</td>
                            <td class="text-end">{!! $supportTicket->status_badge !!}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Created</td>
                            <td class="text-end">{{ $supportTicket->created_at->format('M d, Y h:i A') }}</td>
                        </tr>
                        @if($supportTicket->resolved_at)
                            <tr>
                                <td class="text-muted">Resolved</td>
                                <td class="text-end">{{ $supportTicket->resolved_at->format('M d, Y h:i A') }}</td>
                            </tr>
                        @endif
                    </table>
                </div>
            </div>

            {{-- Assignment --}}
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Assignment</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.support-tickets.assign', $supportTicket) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Assigned To</label>
                            <select name="assigned_to" class="form-select">
                                <option value="">Unassigned</option>
                                @foreach($staff as $s)
                                    <option value="{{ $s->id }}" {{ $supportTicket->assigned_to == $s->id ? 'selected' : '' }}>
                                        {{ $s->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button class="btn btn-primary btn-sm w-100">
                            <i class="fas fa-user-check me-1"></i>Update Assignment
                        </button>
                    </form>
                </div>
            </div>

            <a href="{{ route('admin.support-tickets.index') }}" class="btn btn-outline-secondary w-100 mt-3">
                <i class="fas fa-arrow-left me-1"></i>Back to Tickets
            </a>
        </div>
    </div>
@endsection