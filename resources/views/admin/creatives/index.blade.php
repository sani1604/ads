{{-- resources/views/admin/creatives/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Creatives')

@section('content')
    {{-- Filters --}}
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Client</label>
                    <select name="client" class="form-select select2">
                        <option value="">All</option>
                        @foreach($clients as $c)
                            <option value="{{ $c->id }}" {{ request('client') == $c->id ? 'selected' : '' }}>
                                {{ $c->company_name ?? $c->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All</option>
                        <option value="pending_approval" {{ request('status')=='pending_approval' ? 'selected' : '' }}>Pending</option>
                        <option value="changes_requested" {{ request('status')=='changes_requested' ? 'selected' : '' }}>Changes Requested</option>
                        <option value="approved" {{ request('status')=='approved' ? 'selected' : '' }}>Approved</option>
                        <option value="published" {{ request('status')=='published' ? 'selected' : '' }}>Published</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Platform</label>
                    <select name="platform" class="form-select">
                        <option value="">All</option>
                        @foreach(['facebook','instagram','google','linkedin','twitter','youtube'] as $p)
                            <option value="{{ $p }}" {{ request('platform')==$p ? 'selected' : '' }}>
                                {{ ucfirst($p) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Service</label>
                    <select name="category" class="form-select">
                        <option value="">All</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category')==$cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Title..." value="{{ request('search') }}">
                </div>
            </form>
        </div>
    </div>

    {{-- Header + Bulk --}}
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h5 class="mb-0">Creatives</h5>
        <div class="d-flex gap-2">
            <form id="bulkApproveForm" action="{{ route('admin.creatives.bulk-approve') }}" method="POST">
                @csrf
                <input type="hidden" name="creative_ids[]" id="bulkApproveIds">
                <button type="button" class="btn btn-success btn-sm" onclick="submitBulkApprove()">
                    <i class="fas fa-check me-1"></i>Approve Selected
                </button>
            </form>
            <a href="{{ route('admin.creatives.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i>Upload Creative
            </a>
        </div>
    </div>

    {{-- Table --}}
    <div class="card">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th width="30">
                            <input type="checkbox" id="selectAll">
                        </th>
                        <th>Creative</th>
                        <th>Client</th>
                        <th>Platform</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($creatives as $creative)
                        <tr>
                            <td>
                                <input type="checkbox" class="row-check" value="{{ $creative->id }}">
                            </td>
                            <td>
                                <a href="{{ route('admin.creatives.show', $creative) }}" class="fw-semibold text-decoration-none">
                                    {{ Str::limit($creative->title, 40) }}
                                </a>
                                <div class="small text-muted">
                                    {{ $creative->serviceCategory->name ?? '-' }}
                                </div>
                            </td>
                            <td>
                                <div class="small fw-semibold">
                                    {{ $creative->user->company_name ?? $creative->user->name }}
                                </div>
                                <div class="small text-muted">{{ $creative->user->email }}</div>
                            </td>
                            <td class="text-capitalize">
                                <i class="{{ $creative->platform_icon }} me-1"></i>{{ $creative->platform }}
                            </td>
                            <td>{!! $creative->status_badge !!}</td>
                            <td>{{ $creative->created_at->format('M d, Y') }}</td>
                            <td class="text-end">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light" data-bs-toggle="dropdown">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.creatives.show', $creative) }}">
                                                <i class="fas fa-eye me-2"></i>View
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.creatives.edit', $creative) }}">
                                                <i class="fas fa-edit me-2"></i>Edit
                                            </a>
                                        </li>
                                        @if($creative->status === 'pending_approval')
                                            <li>
                                                <form action="{{ route('admin.creatives.approve', $creative) }}" method="POST">
                                                    @csrf
                                                    <button class="dropdown-item">
                                                        <i class="fas fa-check me-2"></i>Approve
                                                    </button>
                                                </form>
                                            </li>
                                            <li>
                                                <button class="dropdown-item text-warning"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#changesModal"
                                                        data-id="{{ $creative->id }}"
                                                        data-title="{{ $creative->title }}">
                                                    <i class="fas fa-edit me-2"></i>Request Changes
                                                </button>
                                            </li>
                                        @endif
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form id="delete-creative-{{ $creative->id }}" action="{{ route('admin.creatives.destroy', $creative) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="dropdown-item text-danger"
                                                        onclick="if(confirm('Delete this creative?')) document.getElementById('delete-creative-{{ $creative->id }}').submit();">
                                                    <i class="fas fa-trash me-2"></i>Delete
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <x-empty-state 
                                    icon="fas fa-paint-brush"
                                    title="No creatives found"
                                    message="Upload creatives on behalf of clients or wait for your team to add them."
                                    :actionText="'Upload Creative'"
                                    :actionUrl="route('admin.creatives.create')"
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($creatives->hasPages())
            <div class="card-footer">
                <x-pagination :paginator="$creatives" />
            </div>
        @endif
    </div>

    {{-- Request Changes Modal --}}
    <div class="modal fade" id="changesModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="changesForm" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Request Changes</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="small text-muted" id="changesTitle"></p>
                        <textarea name="feedback" rows="4" class="form-control" placeholder="Explain what needs to be changed..." required></textarea>
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

@push('scripts')
<script>
    // Select all
    const selectAll = document.getElementById('selectAll');
    const rowChecks = document.querySelectorAll('.row-check');
    if (selectAll) {
        selectAll.addEventListener('change', () => {
            rowChecks.forEach(ch => ch.checked = selectAll.checked);
        });
    }

    function submitBulkApprove() {
        const ids = Array.from(rowChecks).filter(ch => ch.checked).map(ch => ch.value);
        if (!ids.length) {
            alert('Select at least one creative.');
            return;
        }
        if (!confirm('Approve selected creatives?')) return;

        const input = document.getElementById('bulkApproveIds');
        input.value = ids.join(',');
        document.getElementById('bulkApproveForm').submit();
    }

    // Request changes modal
    const changesModal = document.getElementById('changesModal');
    changesModal?.addEventListener('show.bs.modal', function (event) {
        const btn = event.relatedTarget;
        const id = btn.getAttribute('data-id');
        const title = btn.getAttribute('data-title');
        document.getElementById('changesTitle').innerText = title;
        const form = document.getElementById('changesForm');
        form.action = `/admin/creatives/${id}/request-changes`;
    });
</script>
@endpush