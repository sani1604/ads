{{-- resources/views/client/leads/index.blade.php --}}
@extends('layouts.client')

@section('title', 'Leads')
@section('page-title', 'Leads')

@section('content')
    {{-- Stats Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-card-value">{{ $stats['total'] ?? 0 }}</div>
                <div class="stat-card-label">Total Leads</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-card-value text-primary">{{ $stats['new'] ?? 0 }}</div>
                <div class="stat-card-label">New Leads</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-card-value text-success">{{ $stats['converted'] ?? 0 }}</div>
                <div class="stat-card-label">Converted</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-card-value text-info">{{ $stats['conversion_rate'] ?? 0 }}%</div>
                <div class="stat-card-label">Conversion Rate</div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('client.leads.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Name, email, phone..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="new" {{ request('status') == 'new' ? 'selected' : '' }}>New</option>
                        <option value="contacted" {{ request('status') == 'contacted' ? 'selected' : '' }}>Contacted</option>
                        <option value="qualified" {{ request('status') == 'qualified' ? 'selected' : '' }}>Qualified</option>
                        <option value="converted" {{ request('status') == 'converted' ? 'selected' : '' }}>Converted</option>
                        <option value="lost" {{ request('status') == 'lost' ? 'selected' : '' }}>Lost</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Source</label>
                    <select name="source" class="form-select">
                        <option value="">All Sources</option>
                        <option value="facebook" {{ request('source') == 'facebook' ? 'selected' : '' }}>Facebook</option>
                        <option value="instagram" {{ request('source') == 'instagram' ? 'selected' : '' }}>Instagram</option>
                        <option value="google" {{ request('source') == 'google' ? 'selected' : '' }}>Google</option>
                        <option value="website" {{ request('source') == 'website' ? 'selected' : '' }}>Website</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Date Range</label>
                    <div class="input-group">
                        <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                        <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                    </div>
                </div>
                <div class="col-md-2 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Filter
                    </button>
                    <a href="{{ route('client.leads.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-redo"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Actions Bar --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <span class="text-muted">{{ $leads->total() }} leads found</span>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('client.leads.export', request()->query()) }}" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-download me-1"></i> Export CSV
            </a>
            <a href="{{ route('client.leads.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i> Add Lead
            </a>
        </div>
    </div>

    {{-- Leads Table --}}
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Lead</th>
                        <th>Contact</th>
                        <th>Source</th>
                        <th>Campaign</th>
                        <th>Status</th>
                        <th>Quality</th>
                        <th>Date</th>
                        <th width="100">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($leads as $lead)
                        <tr>
                            <td>
                                <a href="{{ route('client.leads.show', $lead) }}" class="fw-medium text-decoration-none">
                                    {{ $lead->name }}
                                </a>
                                @if($lead->city)
                                    <small class="text-muted d-block">
                                        <i class="fas fa-map-marker-alt me-1"></i>{{ $lead->city }}
                                    </small>
                                @endif
                            </td>
                            <td>
                                @if($lead->phone)
                                    <a href="tel:{{ $lead->phone }}" class="text-decoration-none">
                                        <i class="fas fa-phone me-1 text-muted"></i>{{ $lead->phone }}
                                    </a>
                                @endif
                                @if($lead->email)
                                    <a href="mailto:{{ $lead->email }}" class="text-decoration-none d-block small">
                                        <i class="fas fa-envelope me-1 text-muted"></i>{{ $lead->email }}
                                    </a>
                                @endif
                            </td>
                            <td>
                                <i class="{{ $lead->source_icon }} me-1"></i>
                                {{ ucfirst($lead->source) }}
                            </td>
                            <td>
                                <small>{{ $lead->campaign_name ?? '-' }}</small>
                            </td>
                            <td>
                                <x-status-badge :status="$lead->status" />
                            </td>
                            <td>
                                @if($lead->quality)
                                    <x-status-badge :status="$lead->quality" />
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <small>{{ $lead->created_at->format('M d, Y') }}</small>
                                <small class="text-muted d-block">{{ $lead->created_at->format('h:i A') }}</small>
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light" data-bs-toggle="dropdown">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('client.leads.show', $lead) }}">
                                                <i class="fas fa-eye me-2"></i>View Details
                                            </a>
                                        </li>
                                        @if($lead->phone)
                                            <li>
                                                <a class="dropdown-item" href="https://wa.me/91{{ $lead->phone }}" target="_blank">
                                                    <i class="fab fa-whatsapp me-2 text-success"></i>WhatsApp
                                                </a>
                                            </li>
                                        @endif
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <button class="dropdown-item" onclick="updateStatus({{ $lead->id }}, 'contacted')">
                                                <i class="fas fa-phone me-2"></i>Mark Contacted
                                            </button>
                                        </li>
                                        <li>
                                            <button class="dropdown-item" onclick="updateStatus({{ $lead->id }}, 'qualified')">
                                                <i class="fas fa-thumbs-up me-2"></i>Mark Qualified
                                            </button>
                                        </li>
                                        <li>
                                            <button class="dropdown-item text-success" onclick="updateStatus({{ $lead->id }}, 'converted')">
                                                <i class="fas fa-check-double me-2"></i>Mark Converted
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <x-empty-state 
                                    icon="fas fa-user-plus"
                                    title="No leads found"
                                    message="Leads from your ad campaigns will appear here automatically."
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination --}}
    <div class="mt-4">
        <x-pagination :paginator="$leads" />
    </div>

    {{-- Status Update Forms (Hidden) --}}
    @foreach($leads as $lead)
        <form id="status-form-{{ $lead->id }}" action="{{ route('client.leads.update-status', $lead) }}" method="POST" class="d-none">
            @csrf
            @method('PATCH')
            <input type="hidden" name="status" id="status-input-{{ $lead->id }}">
        </form>
    @endforeach
@endsection

@push('scripts')
<script>
function updateStatus(leadId, status) {
    if (confirm('Are you sure you want to update the status?')) {
        document.getElementById('status-input-' + leadId).value = status;
        document.getElementById('status-form-' + leadId).submit();
    }
}
</script>
@endpush