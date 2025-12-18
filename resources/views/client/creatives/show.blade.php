{{-- resources/views/client/creatives/show.blade.php --}}
@extends('layouts.client')

@section('title', $creative->title)
@section('page-title', 'Creative Details')

@push('styles')
<style>
    .creative-preview {
        max-height: 500px;
        object-fit: contain;
        background: #f1f5f9;
        border-radius: 12px;
    }
    
    .file-thumbnail {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border-radius: 8px;
        cursor: pointer;
        border: 2px solid transparent;
        transition: border-color 0.2s;
    }
    
    .file-thumbnail:hover,
    .file-thumbnail.active {
        border-color: var(--primary-color);
    }
    
    .comment-pin {
        position: absolute;
        width: 24px;
        height: 24px;
        background: #ef4444;
        border-radius: 50%;
        color: white;
        font-size: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transform: translate(-50%, -50%);
    }
</style>
@endpush

@section('content')
    <div class="row g-4">
        {{-- Main Content --}}
        <div class="col-lg-8">
            {{-- Creative Preview --}}
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ $creative->title }}</h5>
                    <x-status-badge :status="$creative->status" />
                </div>
                <div class="card-body text-center position-relative" id="previewContainer">
                    @if($creative->primary_file)
                        @if($creative->primary_file->is_image)
                            <img src="{{ $creative->primary_file->url }}" alt="{{ $creative->title }}" 
                                 class="creative-preview w-100" id="mainPreview">
                        @elseif($creative->primary_file->is_video)
                            <video src="{{ $creative->primary_file->url }}" controls class="creative-preview w-100"></video>
                        @else
                            <div class="py-5">
                                <i class="fas fa-file fa-5x text-muted mb-3"></i>
                                <p>{{ $creative->primary_file->original_name }}</p>
                            </div>
                        @endif
                    @else
                        <div class="py-5">
                            <i class="fas fa-image fa-5x text-muted"></i>
                            <p class="text-muted mt-2">No preview available</p>
                        </div>
                    @endif
                </div>
                
                {{-- File Thumbnails (if multiple) --}}
                @if($creative->files->count() > 1)
                    <div class="card-footer bg-white">
                        <div class="d-flex gap-2 overflow-auto py-2">
                            @foreach($creative->files as $file)
                                @if($file->is_image)
                                    <img src="{{ $file->url }}" alt="" class="file-thumbnail {{ $loop->first ? 'active' : '' }}"
                                         onclick="changePreview(this, '{{ $file->url }}')">
                                @else
                                    <div class="file-thumbnail bg-light d-flex align-items-center justify-content-center">
                                        <i class="fas fa-file text-muted"></i>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            {{-- Ad Copy --}}
            @if($creative->ad_copy)
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">Ad Copy</h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-0">{!! nl2br(e($creative->ad_copy)) !!}</p>
                    </div>
                </div>
            @endif

            {{-- Comments Section --}}
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Comments & Feedback</h6>
                    <span class="badge bg-secondary">{{ $creative->allComments->count() }}</span>
                </div>
                <div class="card-body">
                    {{-- Existing Comments --}}
                    @forelse($creative->comments as $comment)
                        <div class="d-flex mb-4 {{ $comment->is_resolved ? 'opacity-50' : '' }}">
                            <img src="{{ $comment->user->avatar_url }}" alt="" class="rounded-circle me-3" width="40" height="40">
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <strong>{{ $comment->user->name }}</strong>
                                        <small class="text-muted ms-2">{{ $comment->created_at->diffForHumans() }}</small>
                                        @if($comment->is_resolved)
                                            <span class="badge bg-success ms-2">Resolved</span>
                                        @endif
                                    </div>
                                    @if(!$comment->is_resolved)
                                        <form action="{{ route('client.creatives.resolve-comment', $comment) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-success">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                                <p class="mb-2">{{ $comment->comment }}</p>
                                
                                {{-- Replies --}}
                                @foreach($comment->replies as $reply)
                                    <div class="d-flex mt-3 ms-4 ps-3 border-start">
                                        <img src="{{ $reply->user->avatar_url }}" alt="" class="rounded-circle me-2" width="32" height="32">
                                        <div>
                                            <strong>{{ $reply->user->name }}</strong>
                                            <small class="text-muted ms-2">{{ $reply->created_at->diffForHumans() }}</small>
                                            <p class="mb-0 mt-1">{{ $reply->comment }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <p class="text-muted text-center py-3">No comments yet.</p>
                    @endforelse

                    <hr>

                    {{-- Add Comment Form --}}
                    <form action="{{ route('client.creatives.add-comment', $creative) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <textarea name="comment" class="form-control" rows="3" placeholder="Add your feedback or comment..." required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane me-1"></i> Post Comment
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="col-lg-4">
            {{-- Action Buttons --}}
            @if($creative->status === 'pending_approval')
                <div class="card mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h6 class="mb-0"><i class="fas fa-clock me-2"></i>Awaiting Your Approval</h6>
                    </div>
                    <div class="card-body">
                        <p class="small text-muted mb-3">Please review this creative and approve it or request changes.</p>
                        
                        <form action="{{ route('client.creatives.approve', $creative) }}" method="POST" class="mb-2">
                            @csrf
                            <button type="submit" class="btn btn-success w-100">
                                <i class="fas fa-check me-1"></i> Approve Creative
                            </button>
                        </form>
                        
                        <button class="btn btn-outline-warning w-100" data-bs-toggle="modal" data-bs-target="#requestChangesModal">
                            <i class="fas fa-edit me-1"></i> Request Changes
                        </button>
                    </div>
                </div>
            @endif

            {{-- Creative Details --}}
            <div class="card mb-4">
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
                            <td class="text-end">
                                <i class="{{ $creative->platform_icon }} me-1"></i>{{ ucfirst($creative->platform) }}
                            </td>
                        </tr>
                        @if($creative->cta_text)
                            <tr>
                                <td class="text-muted">CTA</td>
                                <td class="text-end">{{ $creative->cta_text }}</td>
                            </tr>
                        @endif
                        @if($creative->landing_url)
                            <tr>
                                <td class="text-muted">Landing URL</td>
                                <td class="text-end">
                                    <a href="{{ $creative->landing_url }}" target="_blank" class="text-truncate d-inline-block" style="max-width: 150px;">
                                        {{ $creative->landing_url }}
                                    </a>
                                </td>
                            </tr>
                        @endif
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

            {{-- Files --}}
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Files</h6>
                    <a href="{{ route('client.creatives.download', $creative) }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-download"></i>
                    </a>
                </div>
                <div class="list-group list-group-flush">
                    @foreach($creative->files as $file)
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div class="text-truncate" style="max-width: 180px;">
                                <i class="fas fa-file me-2 text-muted"></i>{{ $file->original_name }}
                            </div>
                            <small class="text-muted">{{ $file->formatted_size }}</small>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Version History --}}
            @if($creative->versions->count() > 0)
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Version History</h6>
                    </div>
                    <div class="list-group list-group-flush">
                        @foreach($creative->versions as $version)
                            <a href="{{ route('client.creatives.show', $version) }}" class="list-group-item list-group-item-action d-flex justify-content-between">
                                <span>Version {{ $version->version }}</span>
                                <x-status-badge :status="$version->status" />
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Back Button --}}
    <div class="mt-4">
        <a href="{{ route('client.creatives.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Creatives
        </a>
    </div>

    {{-- Request Changes Modal --}}
    <div class="modal fade" id="requestChangesModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('client.creatives.request-changes', $creative) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Request Changes</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">What changes would you like?</label>
                            <textarea name="feedback" class="form-control" rows="4" required placeholder="Please describe the changes you need..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-paper-plane me-1"></i> Submit Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
function changePreview(thumb, url) {
    document.querySelectorAll('.file-thumbnail').forEach(t => t.classList.remove('active'));
    thumb.classList.add('active');
    document.getElementById('mainPreview').src = url;
}
</script>
@endpush