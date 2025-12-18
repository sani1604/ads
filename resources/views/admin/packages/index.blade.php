{{-- resources/views/admin/packages/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Packages')

@section('content')
    {{-- Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-4 col-md-3">
            <div class="stat-card">
                <div class="stat-card-value">{{ $stats['total'] ?? 0 }}</div>
                <div class="stat-card-label">Total Packages</div>
            </div>
        </div>
        <div class="col-4 col-md-3">
            <div class="stat-card">
                <div class="stat-card-value text-success">{{ $stats['active'] ?? 0 }}</div>
                <div class="stat-card-label">Active</div>
            </div>
        </div>
        <div class="col-4 col-md-3">
            <div class="stat-card">
                <div class="stat-card-value">{{ $stats['subscriptions'] ?? 0 }}</div>
                <div class="stat-card-label">Active Subs</div>
            </div>
        </div>
    </div>

    {{-- Filters & Actions --}}
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Category</label>
                    <select name="category" class="form-select">
                        <option value="">All</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Name..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button class="btn btn-primary flex-fill">
                        <i class="fas fa-filter me-1"></i>Filter
                    </button>
                    <a href="{{ route('admin.packages.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-redo"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h5 class="mb-0">Packages</h5>
        <a href="{{ route('admin.packages.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i>Add Package
        </a>
    </div>

    {{-- Packages Table --}}
    <div class="card">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Package</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Billing</th>
                        <th>Featured</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($packages as $package)
                        <tr>
                            <td>
                                <a href="{{ route('admin.packages.show', $package) }}" class="fw-semibold text-decoration-none">
                                    {{ $package->name }}
                                </a>
                                <div class="text-muted small">
                                    slug: {{ $package->slug }}
                                </div>
                            </td>
                            <td>{{ $package->serviceCategory->name ?? '-' }}</td>
                            <td>
                                ₹{{ number_format($package->price, 0) }}
                                @if($package->has_discount)
                                    <span class="text-muted text-decoration-line-through small ms-1">
                                        ₹{{ number_format($package->original_price, 0) }}
                                    </span>
                                @endif
                            </td>
                            <td class="text-capitalize">{{ $package->billing_cycle }}</td>
                            <td>
                                @if($package->is_featured)
                                    <span class="badge bg-primary">Featured</span>
                                @else
                                    <span class="badge bg-light text-muted">No</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $package->is_active ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $package->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light" data-bs-toggle="dropdown">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.packages.show', $package) }}">
                                                <i class="fas fa-eye me-2"></i>View
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.packages.edit', $package) }}">
                                                <i class="fas fa-edit me-2"></i>Edit
                                            </a>
                                        </li>
                                        <li>
                                            <form action="{{ route('admin.packages.toggle-status', $package) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="dropdown-item">
                                                    <i class="fas fa-toggle-on me-2"></i>
                                                    {{ $package->is_active ? 'Deactivate' : 'Activate' }}
                                                </button>
                                            </form>
                                        </li>
                                        <li>
                                            <form action="{{ route('admin.packages.toggle-featured', $package) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="dropdown-item">
                                                    <i class="fas fa-star me-2"></i>
                                                    {{ $package->is_featured ? 'Unfeature' : 'Mark Featured' }}
                                                </button>
                                            </form>
                                        </li>
                                        <li>
                                            <form action="{{ route('admin.packages.duplicate', $package) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="dropdown-item">
                                                    <i class="fas fa-copy me-2"></i>Duplicate
                                                </button>
                                            </form>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form id="delete-package-{{ $package->id }}" action="{{ route('admin.packages.destroy', $package) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="dropdown-item text-danger"
                                                        onclick="if(confirm('Delete this package?')) document.getElementById('delete-package-{{ $package->id }}').submit();">
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
                                    icon="fas fa-cubes"
                                    title="No packages"
                                    message="Create your first package to start selling subscriptions."
                                    :actionText="'Add Package'"
                                    :actionUrl="route('admin.packages.create')"
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($packages->hasPages())
            <div class="card-footer">
                <x-pagination :paginator="$packages" />
            </div>
        @endif
    </div>
@endsection