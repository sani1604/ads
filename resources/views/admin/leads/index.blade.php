{{-- resources/views/admin/leads/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Leads')

@section('content')
    {{-- Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-4 col-md-2">
            <div class="stat-card">
                <div class="stat-card-value">{{ $stats['total'] ?? 0 }}</div>
                <div class="stat-card-label">Total</div>
            </div>
        </div>
        <div class="col-4 col-md-2">
            <div class="stat-card">
                <div class="stat-card-value text-primary">{{ $stats['today'] ?? 0 }}</div>
                <div class="stat-card-label">Today</div>
            </div>
        </div>
        <div class="col-4 col-md-2">
            <div class="stat-card">
                <div class="stat-card-value text-success">{{ $stats['converted'] ?? 0 }}</div>
                <div class="stat-card-label">Converted</div>
            </div>
        </div>
    </div>

    {{-- Filters & actions --}}
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Client</label>
                    <select name="client" class="form-select select2">
                        <option value="">All</option>
                        @foreach($clients as $c)
                            <option value="{{ $c->id }}" {{ request('client')==$c->id ? 'selected' : '' }}>
                                {{ $c->company_name ?? $c->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All</option>
                        @foreach(['new','contacted','qualified','converted','lost','spam'] as $s)
                            <option value="{{ $s }}" {{ request('status')==$s ? 'selected' : '' }}>
                                {{ ucfirst($s) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Source</label>
                    <select name="source" class="form-select">
                        <option value="">All</option>
                        @foreach(['facebook','instagram','google','linkedin','website','manual','other'] as $s)
                            <option value="{{ $s }}" {{ request('source')==$s ? 'selected' : '' }}>
                                {{ ucfirst($s) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Date Range</label>
                    <div class="input-group">
                        <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                        <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Name/email" value="{{ request('search') }}">
                </div>
            </form>
        </div>
    </div>

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h5 class="mb-0">Leads</h5>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.leads.analytics', request()->query()) }}" 
               class="btn btn-outline-secondary btn-sm"
               id="analyticsBtn">
                <i class="fas fa-chart-line me-1"></i>Analytics
            </a>
            <a href="{{ route('admin.leads.import-form') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-file-import me-1"></i>Import
            </a>
            <a href="{{ route('admin.leads.export', request()->query()) }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-download me-1"></i>Export CSV
            </a>
            <a href="{{ route('admin.leads.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i>Add Lead
            </a>
        </div>
    </div>

    {{-- Table --}}
    <div class="card">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Lead</th>
                        <th>Client</th>
                        <th>Source</th>
                        <th>Status</th>
                        <th>Quality</th>
                        <th>Date</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($leads as $lead)
                        <tr>
                            <td>
                                <a href="{{ route('admin.leads.show', $lead) }}" class="fw-semibold text-decoration-none">
                                    {{ $lead->name }}
                                </a>
                                @if($lead->phone)
                                    <div class="small text-muted">{{ $lead->phone }}</div>
                                @endif
                                @if($lead->email)
                                    <div class="small text-muted">{{ $lead->email }}</div>
                                @endif
                            </td>
                            <td>
                                <div class="small fw-semibold">
                                    {{ $lead->user->company_name ?? $lead->user->name }}
                                </div>
                            </td>
                            <td>
                                <i class="{{ $lead->source_icon }} me-1"></i>
                                <span class="text-capitalize small">{{ $lead->source }}</span>
                            </td>
                            <td>{!! $lead->status_badge !!}</td>
                            <td>{!! $lead->quality_badge ?? '<span class="text-muted small">N/A</span>' !!}</td>
                            <td>
                                <small>{{ $lead->created_at->format('M d, Y') }}</small>
                            </td>
                            <td class="text-end">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light" data-bs-toggle="dropdown">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.leads.show', $lead) }}">
                                                <i class="fas fa-eye me-2"></i>View
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.leads.edit', $lead) }}">
                                                <i class="fas fa-edit me-2"></i>Edit
                                            </a>
                                        </li>
                                        <li>
                                            <button class="dropdown-item" onclick="updateLeadStatus({{ $lead->id }}, 'converted')">
                                                <i class="fas fa-check-double me-2"></i>Mark Converted
                                            </button>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form id="delete-lead-{{ $lead->id }}" action="{{ route('admin.leads.destroy', $lead) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="dropdown-item text-danger"
                                                        onclick="if(confirm('Delete this lead?')) document.getElementById('delete-lead-{{ $lead->id }}').submit();">
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
                                    icon="fas fa-user-plus"
                                    title="No leads"
                                    message="Leads from Meta/Google webhooks and manual entries will appear here."
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($leads->hasPages())
            <div class="card-footer">
                <x-pagination :paginator="$leads" />
            </div>
        @endif
    </div>

    {{-- Hidden status form (AJAX alternative) --}}
    <form id="status-form" method="POST" class="d-none">
        @csrf
        @method('PATCH')
        <input type="hidden" name="status" id="status-input">
    </form>
@endsection

@push('scripts')
<script>
function updateLeadStatus(id, status) {
    if (!confirm('Update lead status to ' + status + '?')) return;
    const form = document.getElementById('status-form');
    form.action = `/admin/leads/${id}/status`;
    document.getElementById('status-input').value = status;
    form.submit();
}
</script>
@endpush