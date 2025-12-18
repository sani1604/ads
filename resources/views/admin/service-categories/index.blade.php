{{-- resources/views/admin/service-categories/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Service Categories')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Service Categories</h5>
        <a href="{{ route('admin.service-categories.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i>Add Category
        </a>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Packages</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $cat)
                        <tr>
                            <td>
                                <i class="{{ $cat->icon }} me-2 text-primary"></i>{{ $cat->name }}
                            </td>
                            <td><code>{{ $cat->slug }}</code></td>
                            <td>{{ $cat->active_packages_count }}</td>
                            <td>
                                <span class="badge {{ $cat->is_active ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $cat->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <a href="{{ route('admin.service-categories.edit', $cat) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.service-categories.toggle-status', $cat) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-secondary">
                                            <i class="fas fa-toggle-on"></i>
                                        </button>
                                    </form>
                                    @if($cat->packages_count == 0)
                                        <form action="{{ route('admin.service-categories.destroy', $cat) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                    onclick="if(confirm('Delete this category?')) this.form.submit();">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <x-empty-state 
                                    icon="fas fa-layer-group"
                                    title="No service categories"
                                    message="Add categories like Meta Ads, Google Ads, SEO, etc."
                                    :actionText="'Add Category'"
                                    :actionUrl="route('admin.service-categories.create')"
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($categories->hasPages())
            <div class="card-footer">
                <x-pagination :paginator="$categories" />
            </div>
        @endif
    </div>
@endsection