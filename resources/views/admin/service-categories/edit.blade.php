{{-- resources/views/admin/service-categories/edit.blade.php --}}
@extends('layouts.admin')

@section('title', 'Edit Service Category')

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Edit Service Category</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.service-categories.update', $serviceCategory) }}" method="POST">
                @csrf
                @method('PUT')

                <x-form.input 
                    name="name"
                    label="Name"
                    :required="true"
                    :value="$serviceCategory->name"
                />

                <x-form.input 
                    name="slug"
                    label="Slug"
                    :required="true"
                    :value="$serviceCategory->slug"
                />

                <x-form.textarea 
                    name="description"
                    label="Description"
                    rows="3"
                    :value="$serviceCategory->description"
                />

                <x-form.input 
                    name="icon"
                    label="Icon (FontAwesome class)"
                    :value="$serviceCategory->icon"
                />

                <x-form.input 
                    name="color"
                    label="Color (hex)"
                    :value="$serviceCategory->color"
                />

                <x-form.input 
                    name="sort_order"
                    label="Sort Order"
                    type="number"
                    :value="$serviceCategory->sort_order"
                />

                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active" {{ $serviceCategory->is_active ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">Active</label>
                </div>

                <button class="btn btn-primary">
                    <i class="fas fa-save me-1"></i>Save Changes
                </button>
                <a href="{{ route('admin.service-categories.index') }}" class="btn btn-outline-secondary ms-2">
                    Cancel
                </a>
            </form>
        </div>
    </div>
@endsection