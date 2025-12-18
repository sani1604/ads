{{-- resources/views/admin/industries/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Industries')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Industries</h5>
        <a href="{{ route('admin.industries.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i>Add Industry
        </a>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Clients</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($industries as $industry)
                        <tr>
                            <td>
                                <i class="{{ $industry->icon_class }} me-2 text-primary"></i>{{ $industry->name }}
                            </td>
                            <td><code>{{ $industry->slug }}</code></td>
                            <td>{{ $industry->clients_count }}</td>
                            <td>
                                <span class="badge {{ $industry->is_active ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $industry->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <a href="{{ route('admin.industries.edit', $industry) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.industries.toggle-status', $industry) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-secondary">
                                            <i class="fas fa-toggle-on"></i>
                                        </button>
                                    </form>
                                    @if($industry->clients_count == 0)
                                        <form action="{{ route('admin.industries.destroy', $industry) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                    onclick="if(confirm('Delete this industry?')) this.form.submit();">
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
                                    icon="fas fa-industry"
                                    title="No industries defined"
                                    message="Add industries to categorize your clients."
                                    :actionText="'Add Industry'"
                                    :actionUrl="route('admin.industries.create')"
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($industries->hasPages())
            <div class="card-footer">
                <x-pagination :paginator="$industries" />
            </div>
        @endif
    </div>
@endsection