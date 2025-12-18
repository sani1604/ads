{{-- resources/views/admin/creatives/show.blade.php --}}
@extends('layouts.admin')

@section('title', $creative->title)

@section('content')
    <div class="row g-4">
        {{-- Preview + Comments (reuse client-style but with admin actions) --}}
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">{{ $creative->title }}</h5>
                        <small class="text-muted">
                            {{ $creative->user->company_name ?? $creative->user->name }} • 
                            {{ $creative->serviceCategory->name ?? '-' }}
                        </small>
                    </div>
                    <div class="text-end">
                        {!! $creative->status_badge !!}
                        @if($creative->status === 'pending_approval')
                            <form action="{{ route('admin.creatives.approve', $creative) }}" method="POST" class="d-inline">
                                @csrf
                                <button class="btn btn-success btn-sm ms-2">
                                    <i class="fas fa-check me-1"></i>Approve
                                </button>
                            </form>
                            <button class="btn btn-warning btn-sm ms-1"
                                    data-bs-toggle="modal"
                                    data-bs-target="#changesModal"
                                    data-id="{{ $creative->id }}">
                                <i class="fas fa-edit me-1"></i>Request Changes
                            </button>
                        @endif
                    </div>
                </div>
                <div class="card-body text-center">
                    @if($creative->primary_file && $creative->primary_file->is_image)
                        <img src="{{ $creative->primary_file->url }}" class="img-fluid rounded" alt="">
                    @elseif($creative->primary_file && $creative->primary_file->is_video)
                        <video src="{{ $creative->primary_file->url }}" controls class="w-100 rounded"></video>
                    @else
                        <p class="text-muted py-5">No preview.</p>
                    @endif
                </div>
            </div>

            {{-- Comments (same as client, plus ability to resolve all) --}}
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Comments ({{ $creative->allComments->count() }})</h6>
                </div>
                <div class="card-body">
                    @forelse($creative->comments as $comment)
                        <div class="mb-3 border-bottom pb-2">
                            <div class="d-flex">
                                <img src="{{ $comment->user->avatar_url }}" class="rounded-circle me-2" width="32" height="32">
                                <div>
                                    <div class="d-flex align-items-center">
                                        <strong>{{ $comment->user->name }}</strong>
                                        <small class="text-muted ms-2">{{ $comment->created_at->diffForHumans() }}</small>
                                        @if($comment->is_resolved)
                                            <span class="badge bg-success ms-2">Resolved</span>
                                        @endif
                                    </div>
                                    <p class="mb-1">{{ $comment->comment }}</p>
                                    @foreach($comment->replies as $reply)
                                        <div class="d-flex mt-2 ms-4 ps-3 border-start">
                                            <img src="{{ $reply->user->avatar_url }}" class="rounded-circle me-2" width="24" height="24">
                                            <div>
                                                <small class="fw-semibold">{{ $reply->user->name }}</small>
                                                <small class="text-muted ms-1">{{ $reply->created_at->diffForHumans() }}</small>
                                                <div>{{ $reply->comment }}</div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted">No comments.</p>
                    @endforelse

                    {{-- Admin add comment --}}
                    <form action="{{ route('admin.creatives.add-comment', $creative) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <textarea name="comment" class="form-control" rows="3" placeholder="Leave internal note or feedback..."></textarea>
                        </div>
                        <button class="btn btn-primary btn-sm">
                            <i class="fas fa-comment me-1"></i>Add Comment
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">Details</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td class="text-muted">Type</td>
                            <td class="text-end">{{ $creative->type_label }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Platform</td>
                            <td class="text-end text-capitalize">{{ $creative->platform }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Version</td>
                            <td class="text-end">v{{ $creative->version }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Created</td>
                            <td class="text-end">{{ $creative->created_at->format('M d, Y') }}</td>
                        </tr>
                        @if($creative->approved_at)
                            <tr>
                                <td class="text-muted">Approved</td>
                                <td class="text-end">{{ $creative->approved_at->format('M d, Y') }}</td>
                            </tr>
                        @endif
                    </table>
                </div>
            </div>

            {{-- File list --}}
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Files</h6>
                </div>
                <div class="list-group list-group-flush">
                    @forelse($creative->files as $file)
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div class="text-truncate" style="max-width: 180px;">
                                <i class="fas fa-file me-1 text-muted"></i>{{ $file->original_name }}
                            </div>
                            <small class="text-muted">{{ $file->formatted_size }}</small>
                        </div>
                    @empty
                        <div class="list-group-item text-muted">No files.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Request Changes Modal --}}
    <div class="modal fade" id="changesModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="adminChangesForm" method="POST" action="{{ route('admin.creatives.request-changes', $creative) }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Request Changes</h5>
                        <button class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <textarea name="feedback" rows="4" class="form-control" placeholder="Describe requested changes..." required></textarea>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-warning">Send Request</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection