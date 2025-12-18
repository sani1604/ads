{{-- resources/views/admin/clients/create.blade.php --}}
@extends('layouts.admin')

@section('title', 'Add Client')

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Add New Client</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.clients.store') }}" method="POST">
                @csrf

                <div class="row g-3">
                    <div class="col-md-6">
                        <x-form.input 
                            name="name"
                            label="Full Name"
                            :required="true"
                        />
                    </div>
                    <div class="col-md-6">
                        <x-form.input 
                            name="email"
                            label="Email"
                            type="email"
                            :required="true"
                        />
                    </div>
                    <div class="col-md-6">
                        <x-form.input 
                            name="phone"
                            label="Phone"
                            :required="true"
                            placeholder="10-digit mobile"
                        />
                    </div>
                    <div class="col-md-6">
                        <x-form.input 
                            name="password"
                            label="Password"
                            type="password"
                            :required="true"
                        />
                    </div>
                    <div class="col-md-6">
                        <x-form.input 
                            name="company_name"
                            label="Company Name"
                        />
                    </div>
                    <div class="col-md-6">
                        <x-form.select 
                            name="industry_id"
                            label="Industry"
                            :options="$industries->pluck('name', 'id')->toArray()"
                            placeholder="Select industry"
                        />
                    </div>
                    <div class="col-md-12">
                        <x-form.textarea 
                            name="address"
                            label="Address"
                            rows="2"
                        />
                    </div>
                    <div class="col-md-4">
                        <x-form.input 
                            name="city"
                            label="City"
                        />
                    </div>
                    <div class="col-md-4">
                        <x-form.input 
                            name="state"
                            label="State"
                        />
                    </div>
                    <div class="col-md-4">
                        <x-form.input 
                            name="postal_code"
                            label="PIN Code"
                        />
                    </div>
                    <div class="col-md-6">
                        <x-form.input 
                            name="gst_number"
                            label="GST Number"
                        />
                    </div>
                    <div class="col-md-6">
                        <label class="form-label d-block">Status</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" checked>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <button class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Create Client
                    </button>
                    <a href="{{ route('admin.clients.index') }}" class="btn btn-outline-secondary ms-2">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection