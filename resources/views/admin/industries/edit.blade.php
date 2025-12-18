{{-- resources/views/admin/industries/edit.blade.php --}}
@extends('layouts.admin')

@section('title', 'Edit Industry')

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Edit Industry</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.industries.update', $industry) }}" method="POST">
                @csrf
                @method('PUT')

                <x-form.input 
                    name="name"
                    label="Name"
                    :required="true"
                    :value="$industry->name"
                />

                <x-form.input 
                    name="slug"
                    label="Slug"
                    :required="true"
                    :value="$industry->slug"
                />

                <x-form.textarea 
                    name="description"
                    label="Description"
                    rows="3"
                    :value="$industry->description"
                />

                <x-form.input 
                    name="icon"
                    label="Icon (FontAwesome suffix)"
                    :value="$industry->icon"
                />

                <x-form.input 
                    name="sort_order"
                    label="Sort Order"
                    type="number"
                    :value="$industry->sort_order"
                />

                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active" {{ $industry->is_active ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">Active</label>
                </div>

                <button class="btn btn-primary">
                    <i class="fas fa-save me-1"></i>Save Changes
                </button>
                <a href="{{ route('admin.industries.index') }}" class="btn btn-outline-secondary ms-2">
                    Cancel
                </a>
            </form>
        </div>
    </div>
@endsection