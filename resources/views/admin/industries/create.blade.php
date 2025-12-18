{{-- resources/views/admin/industries/create.blade.php --}}
@extends('layouts.admin')

@section('title', 'Add Industry')

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Add Industry</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.industries.store') }}" method="POST">
                @csrf

                <x-form.input 
                    name="name"
                    label="Name"
                    :required="true"
                />

                <x-form.input 
                    name="slug"
                    label="Slug"
                    :required="true"
                    help="Example: real-estate, healthcare"
                />

                <x-form.textarea 
                    name="description"
                    label="Description"
                    rows="3"
                />

                <x-form.input 
                    name="icon"
                    label="Icon (FontAwesome suffix)"
                    :value="'building'"
                    help="Used as `fa-solid fa-{icon}`, e.g. building, heart-pulse"
                />

                <x-form.input 
                    name="sort_order"
                    label="Sort Order"
                    type="number"
                    :value="0"
                />

                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active" checked>
                    <label class="form-check-label" for="is_active">Active</label>
                </div>

                <button class="btn btn-primary">
                    <i class="fas fa-save me-1"></i>Save
                </button>
                <a href="{{ route('admin.industries.index') }}" class="btn btn-outline-secondary ms-2">
                    Cancel
                </a>
            </form>
        </div>
    </div>
@endsection