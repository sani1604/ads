{{-- resources/views/admin/leads/show.blade.php --}}
@extends('layouts.admin')

@section('title', 'Lead ' . $lead->lead_id)

@section('content')
    <div class="row g-4">
        <div class="col-lg-8">
            {{-- Lead details (similar to client lead show) --}}
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">{{ $lead->name }}</h5>
                        <small class="text-muted">
                            {{ $lead->user->company_name ?? $lead->user->name }} • {{ ucfirst($lead->source) }}
                        </small>
                    </div>
                    <div class="text-end">
                        {!! $lead->status_badge !!}
                        @if($lead->quality_badge)
                            {!! $lead->quality_badge !!}
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">Contact</h6>
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <td class="text-muted">Phone</td>
                                    <td class="text-end">{{ $lead->phone ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Email</td>
                                    <td class="text-end">{{ $lead->email ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Location</td>
                                    <td class="text-end">
                                        {{ $lead->city ?? '-' }}{{ $lead->state ? ', '.$lead->state : '' }}
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">Campaign</h6>
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <td class="text-muted">Campaign</td>
                                    <td class="text-end">{{ $lead->campaign_name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Ad Name</td>
                                    <td class="text-end">{{ $lead->ad_name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Form</td>
                                    <td class="text-end">{{ $lead->form_name ?? '-' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    {{-- Custom fields --}}
                    @if($lead->custom_fields)
                        <hr>
                        <h6 class="text-muted mb-2">Additional Fields</h6>
                        <div class="row g-2">
                            @foreach($lead->custom_fields as $k => $v)
                                <div class="col-md-6">
                                    <div class="border rounded p-2">
                                        <small class="text-muted d-block">{{ ucwords(str_replace('_',' ',$k)) }}</small>
                                        <span>{{ $v }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- Notes (reuse editing style) --}}
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Notes</h6>
                </div>
                <div class="card-body">
                    @if($lead->notes)
                        <pre class="bg-light p-3 rounded small mb-3" style="white-space: pre-wrap;">{{ $lead->notes }}</pre>
                    @else
                        <p class="text-muted">No notes.</p>
                    @endif

                    <form action="{{ route('admin.leads.add-note', $lead) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <textarea name="note" class="form-control" rows="3" placeholder="Add note with time stamp..." required></textarea>
                        </div>
                        <button class="btn btn-primary btn-sm">
                            <i class="fas fa-plus me-1"></i>Add Note
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Sidebar: status/quality --}}
        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">Update Status</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.leads.update-status', $lead) }}" method="POST" class="mb-3">
                        @csrf
                        @method('PATCH')
                        <select name="status" class="form-select mb-2">
                            @foreach(['new','contacted','qualified','converted','lost','spam'] as $s)
                                <option value="{{ $s }}" {{ $lead->status==$s ? 'selected' : '' }}>
                                    {{ ucfirst($s) }}
                                </option>
                            @endforeach
                        </select>
                        <button class="btn btn-primary w-100 btn-sm">Save Status</button>
                    </form>

                    <form action="{{ route('admin.leads.update-quality', $lead) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <div class="d-flex gap-2">
                            <button name="quality" value="hot" class="btn btn-sm {{ $lead->quality=='hot' ? 'btn-danger' : 'btn-outline-danger' }} flex-fill">
                                Hot
                            </button>
                            <button name="quality" value="warm" class="btn btn-sm {{ $lead->quality=='warm' ? 'btn-warning' : 'btn-outline-warning' }} flex-fill">
                                Warm
                            </button>
                            <button name="quality" value="cold" class="btn btn-sm {{ $lead->quality=='cold' ? 'btn-info' : 'btn-outline-info' }} flex-fill">
                                Cold
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Meta --}}
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Meta</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td class="text-muted">Lead ID</td>
                            <td class="text-end"><code>{{ $lead->lead_id }}</code></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Created</td>
                            <td class="text-end">{{ $lead->created_at->format('M d, Y h:i A') }}</td>
                        </tr>
                        @if($lead->contacted_at)
                            <tr>
                                <td class="text-muted">Contacted</td>
                                <td class="text-end">{{ $lead->contacted_at->format('M d, Y h:i A') }}</td>
                            </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>

    <a href="{{ route('admin.leads.index') }}" class="btn btn-outline-secondary mt-3">
        <i class="fas fa-arrow-left me-1"></i>Back to Leads
    </a>
@endsection