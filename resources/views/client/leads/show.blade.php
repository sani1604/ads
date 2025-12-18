{{-- resources/views/client/leads/show.blade.php --}}
@extends('layouts.client')

@section('title', 'Lead Details')
@section('page-title', 'Lead Details')

@section('content')
    <div class="row g-4">
        {{-- Main Content --}}
        <div class="col-lg-8">
            {{-- Lead Info Card --}}
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ $lead->name }}</h5>
                    <x-status-badge :status="$lead->status" />
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-3">Contact Information</h6>
                            <table class="table table-borderless table-sm">
                                <tr>
                                    <td width="120" class="text-muted">Phone</td>
                                    <td>
                                        @if($lead->phone)
                                            <a href="tel:{{ $lead->phone }}">{{ $lead->phone }}</a>
                                            <a href="https://wa.me/91{{ $lead->phone }}" target="_blank" class="ms-2 text-success">
                                                <i class="fab fa-whatsapp"></i>
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Email</td>
                                    <td>
                                        @if($lead->email)
                                            <a href="mailto:{{ $lead->email }}">{{ $lead->email }}</a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Location</td>
                                    <td>{{ $lead->city ?? '-' }}{{ $lead->state ? ', ' . $lead->state : '' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-3">Lead Source</h6>
                            <table class="table table-borderless table-sm">
                                <tr>
                                    <td width="120" class="text-muted">Source</td>
                                    <td><i class="{{ $lead->source_icon }} me-1"></i> {{ ucfirst($lead->source) }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Campaign</td>
                                    <td>{{ $lead->campaign_name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Ad Name</td>
                                    <td>{{ $lead->ad_name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Form</td>
                                    <td>{{ $lead->form_name ?? '-' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    {{-- Custom Fields --}}
                    @if($lead->custom_fields && count($lead->custom_fields) > 0)
                        <hr>
                        <h6 class="text-muted mb-3">Additional Information</h6>
                        <div class="row g-3">
                            @foreach($lead->custom_fields as $key => $value)
                                <div class="col-md-6">
                                    <div class="border rounded p-2">
                                        <small class="text-muted d-block">{{ ucwords(str_replace('_', ' ', $key)) }}</small>
                                        <span>{{ $value }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- Notes Section --}}
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Notes</h5>
                </div>
                <div class="card-body">
                    @if($lead->notes)
                        <div class="bg-light rounded p-3 mb-3">
                            {!! nl2br(e($lead->notes)) !!}
                        </div>
                    @else
                        <p class="text-muted mb-3">No notes added yet.</p>
                    @endif

                    <form action="{{ route('client.leads.add-note', $lead) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <textarea name="note" class="form-control" rows="3" placeholder="Add a note..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus me-1"></i> Add Note
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="col-lg-4">
            {{-- Quick Actions --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Quick Actions</h6>
                </div>
                <div class="card-body">
                    @if($lead->phone)
                        <a href="tel:{{ $lead->phone }}" class="btn btn-outline-primary w-100 mb-2">
                            <i class="fas fa-phone me-2"></i>Call
                        </a>
                        <a href="https://wa.me/91{{ $lead->phone }}" target="_blank" class="btn btn-success w-100 mb-2">
                            <i class="fab fa-whatsapp me-2"></i>WhatsApp
                        </a>
                    @endif
                    @if($lead->email)
                        <a href="mailto:{{ $lead->email }}" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-envelope me-2"></i>Email
                        </a>
                    @endif
                </div>
            </div>

            {{-- Update Status --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Update Status</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('client.leads.update-status', $lead) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <div class="mb-3">
                            <select name="status" class="form-select">
                                <option value="new" {{ $lead->status == 'new' ? 'selected' : '' }}>New</option>
                                <option value="contacted" {{ $lead->status == 'contacted' ? 'selected' : '' }}>Contacted</option>
                                <option value="qualified" {{ $lead->status == 'qualified' ? 'selected' : '' }}>Qualified</option>
                                <option value="converted" {{ $lead->status == 'converted' ? 'selected' : '' }}>Converted</option>
                                <option value="lost" {{ $lead->status == 'lost' ? 'selected' : '' }}>Lost</option>
                                <option value="spam" {{ $lead->status == 'spam' ? 'selected' : '' }}>Spam</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Update Status</button>
                    </form>
                </div>
            </div>

            {{-- Update Quality --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Lead Quality</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('client.leads.update-quality', $lead) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <div class="d-flex gap-2">
                            <button type="submit" name="quality" value="hot" class="btn {{ $lead->quality == 'hot' ? 'btn-danger' : 'btn-outline-danger' }} flex-fill">
                                <i class="fas fa-fire me-1"></i>Hot
                            </button>
                            <button type="submit" name="quality" value="warm" class="btn {{ $lead->quality == 'warm' ? 'btn-warning' : 'btn-outline-warning' }} flex-fill">
                                <i class="fas fa-temperature-half me-1"></i>Warm
                            </button>
                            <button type="submit" name="quality" value="cold" class="btn {{ $lead->quality == 'cold' ? 'btn-info' : 'btn-outline-info' }} flex-fill">
                                <i class="fas fa-snowflake me-1"></i>Cold
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Lead Info --}}
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Lead Info</h6>
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

    {{-- Back Button --}}
    <div class="mt-4">
        <a href="{{ route('client.leads.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Leads
        </a>
    </div>
@endsection