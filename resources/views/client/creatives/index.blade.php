{{-- resources/views/client/creatives/index.blade.php --}}
@extends('layouts.client')

@section('title', 'Creatives')
@section('page-title', 'Creatives')

@section('content')
    {{-- Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-card-value">{{ $stats['total'] }}</div>
                <div class="stat-card-label">Total Creatives</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-card-value text-warning">{{ $stats['pending'] }}</div>
                <div class="stat-card-label">Pending Approval</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-card-value text-success">{{ $stats['approved'] }}</div>
                <div class="stat-card-label">Approved</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-card-value text-info">{{ $stats['changes_requested'] }}</div>
                <div class="stat-card-label">Changes Requested</div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="Search creatives..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="pending_approval" {{ request('status') == 'pending_approval' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="changes_requested" {{ request('status') == 'changes_requested' ? 'selected' : '' }}>Changes Requested</option>
                        <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="platform" class="form-select">
                        <option value="">All Platforms</option>
                        <option value="facebook" {{ request('platform') == 'facebook' ? 'selected' : '' }}>Facebook</option>
                        <option value="instagram" {{ request('platform') == 'instagram' ? 'selected' : '' }}>Instagram</option>
                        <option value="google" {{ request('platform') == 'google' ? 'selected' : '' }}>Google</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-1"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Creatives Grid --}}
    <div class="row g-4">
        @forelse($creatives as $creative)
            <div class="col-md-6 col-lg-4">
                <div class="card h-100">
                    {{-- Thumbnail --}}
                    <div class="position-relative" style="height: 200px; overflow: hidden;">
                        @if($creative->thumbnail_url)
                            <img src="{{ $creative->thumbnail_url }}" alt="{{ $creative->title }}" 
                                 class="w-100 h-100" style="object-fit: cover;">
                        @else
                            <div class="w-100 h-100 bg-light d-flex align-items-center justify-content-center">
                                <i class="fas fa-image fa-3x text-muted"></i>
                            </div>
                        @endif
                        
                        {{-- Status Badge --}}
                        <div class="position-absolute top-0 end-0 m-2">
                            <x-status-badge :status="$creative->status" />
                        </div>
                        
                        {{-- Platform Badge --}}
                        <div class="position-absolute bottom-0 start-0 m-2">
                            <span class="badge bg-dark">
                                <i class="{{ $creative->platform_icon }} me-1"></i>{{ ucfirst($creative->platform) }}
                            </span>
                        </div>
                    </div>

                    <div class="card-body">
                        <h6 class="card-title mb-1">
                            <a href="{{ route('client.creatives.show', $creative) }}" class="text-decoration-none">
                                {{ $creative->title }}
                            </a>
                        </h6>
                        <small class="text-muted">
                            {{ $creative->type_label }} • {{ $creative->created_at->format('M d, Y') }}
                        </small>
                        
                        @if($creative->unresolved_comments_count > 0)
                            <div class="mt-2">
                                <span class="badge bg-warning text-dark">
                                    <i class="fas fa-comment me-1"></i>{{ $creative->unresolved_comments_count }} comments
                                </span>
                            </div>
                        @endif
                    </div>

                    <div class="card-footer bg-white border-top-0">
                        <div class="d-flex gap-2">
                            <a href="{{ route('client.creatives.show', $creative) }}" class="btn btn-outline-primary btn-sm flex-fill">
                                <i class="fas fa-eye me-1"></i> View
                            </a>
                            @if($creative->status === 'pending_approval')
                                <button class="btn btn-success btn-sm" onclick="approveCreative({{ $creative->id }})">
                                    <i class="fas fa-check"></i>
                                </button>
                            @endif
                            <a href="{{ route('client.creatives.download', $creative) }}" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-download"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <x-empty-state 
                    icon="fas fa-paint-brush"
                    title="No creatives yet"
                    message="Creatives uploaded by our team for your approval will appear here."
                />
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="mt-4">
        <x-pagination :paginator="$creatives" />
    </div>

    {{-- Approve Modal --}}
    <div class="modal fade" id="approveModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Approve Creative</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to approve this creative?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form id="approveForm" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check me-1"></i> Approve
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
function approveCreative(id) {
    document.getElementById('approveForm').action = `/client/creatives/${id}/approve`;
    new bootstrap.Modal(document.getElementById('approveModal')).show();
}
</script>
@endpush