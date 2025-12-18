{{-- resources/views/admin/packages/show.blade.php --}}
@extends('layouts.admin')

@section('title', $package->name)

@section('content')
    <div class="card mb-4">
        <div class="card-body d-flex justify-content-between align-items-start">
            <div>
                <h4 class="mb-1">{{ $package->name }}</h4>
                <p class="text-muted mb-2">{{ $package->serviceCategory->name ?? '' }}</p>
                <div class="mb-2">
                    <span class="badge {{ $package->is_active ? 'bg-success' : 'bg-secondary' }}">
                        {{ $package->is_active ? 'Active' : 'Inactive' }}
                    </span>
                    @if($package->is_featured)
                        <span class="badge bg-primary ms-1">Featured</span>
                    @endif
                </div>
                <p class="mb-0">{{ $package->short_description }}</p>
            </div>
            <div class="text-end">
                <h3 class="mb-0">₹{{ number_format($package->price, 0) }}</h3>
                <small class="text-muted text-capitalize">/ {{ $package->billing_cycle }}</small>
                @if($package->has_discount)
                    <div>
                        <span class="text-muted text-decoration-line-through">
                            ₹{{ number_format($package->original_price, 0) }}
                        </span>
                        <span class="badge bg-success ms-1">
                            -{{ $package->discount_percentage }}%
                        </span>
                    </div>
                @endif
                <div class="mt-3">
                    <a href="{{ route('admin.packages.edit', $package) }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-edit me-1"></i>Edit
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- SAFE ARRAY CONVERSION --}}
    @php
        function toSafeArray($value) {
            if (empty($value)) return [];

            if (is_array($value)) return $value;

            if (is_string($value)) {
                $json = json_decode($value, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
                    return $json;
                }
                return array_map('trim', explode(',', $value));
            }

            return [];
        }

        $features = toSafeArray($package->features);
        $deliverables = toSafeArray($package->deliverables);
    @endphp

    {{-- Details + Subscribers --}}
    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <h6 class="mb-0">Details</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-3">
                        <tr>
                            <td class="text-muted">Slug</td>
                            <td class="text-end">{{ $package->slug }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Industry</td>
                            <td class="text-end">{{ $package->industry?->name ?? 'All' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Billing Days</td>
                            <td class="text-end">{{ $package->billing_cycle_days }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Max Creatives / Month</td>
                            <td class="text-end">{{ $package->max_creatives_per_month }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Max Revisions</td>
                            <td class="text-end">{{ $package->max_revisions }}</td>
                        </tr>
                    </table>

                    <h6 class="text-muted">Features</h6>
                    <ul class="small">
                        @foreach($features as $f)
                            <li>{{ $f }}</li>
                        @endforeach
                    </ul>

                    <h6 class="text-muted mt-3">Deliverables</h6>
                    <ul class="small">
                        @foreach($deliverables as $d)
                            <li>{{ $d }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        {{-- Active Subscriptions --}}
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Active Subscribers</h6>
                    <span class="badge bg-primary">{{ $activeSubscriptions->count() }}</span>
                </div>
                <div class="list-group list-group-flush">
                    @forelse($activeSubscriptions as $sub)
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-semibold">
                                    {{ $sub->user->company_name ?? $sub->user->name }}
                                </div>
                                <small class="text-muted">
                                    {{ $sub->start_date->format('M d, Y') }} – {{ $sub->end_date->format('M d, Y') }}
                                </small>
                            </div>
                            <span class="badge bg-success">
                                ₹{{ number_format($sub->total_amount, 0) }}
                            </span>
                        </div>
                    @empty
                        <div class="list-group-item text-muted">No active subscribers.</div>
                    @endforelse
                </div>
                <div class="card-footer">
                    <small class="text-muted">
                        Total revenue (all time) from this package:
                        ₹{{ number_format($totalRevenue ?? 0, 0) }}
                    </small>
                </div>
            </div>
        </div>
    </div>

    {{-- Back --}}
    <div class="mt-4">
        <a href="{{ route('admin.packages.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back to Packages
        </a>
    </div>
@endsection
