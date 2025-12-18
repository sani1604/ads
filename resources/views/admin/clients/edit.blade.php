{{-- resources/views/admin/clients/edit.blade.php --}}
@extends('layouts.admin')

@section('title', 'Edit Client')

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Edit Client</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.clients.update', $client) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-md-6">
                        <x-form.input 
                            name="name"
                            label="Full Name"
                            :required="true"
                            :value="$client->name"
                        />
                    </div>
                    <div class="col-md-6">
                        <x-form.input 
                            name="email"
                            label="Email"
                            type="email"
                            :required="true"
                            :value="$client->email"
                        />
                    </div>
                    <div class="col-md-6">
                        <x-form.input 
                            name="phone"
                            label="Phone"
                            :required="true"
                            :value="$client->phone"
                        />
                    </div>
                    <div class="col-md-6">
                        <x-form.input 
                            name="company_name"
                            label="Company Name"
                            :value="$client->company_name"
                        />
                    </div>
                    <div class="col-md-6">
                        <x-form.select 
                            name="industry_id"
                            label="Industry"
                            :options="$industries->pluck('name', 'id')->toArray()"
                            :selected="$client->industry_id"
                            placeholder="Select industry"
                        />
                    </div>
                    <div class="col-md-12">
                        <x-form.textarea 
                            name="address"
                            label="Address"
                            rows="2"
                            :value="$client->address"
                        />
                    </div>
                    <div class="col-md-4">
                        <x-form.input 
                            name="city"
                            label="City"
                            :value="$client->city"
                        />
                    </div>
                    <div class="col-md-4">
                        <x-form.input 
                            name="state"
                            label="State"
                            :value="$client->state"
                        />
                    </div>
                    <div class="col-md-4">
                        <x-form.input 
                            name="postal_code"
                            label="PIN Code"
                            :value="$client->postal_code"
                        />
                    </div>
                    <div class="col-md-6">
                        <x-form.input 
                            name="gst_number"
                            label="GST Number"
                            :value="$client->gst_number"
                        />
                    </div>
                    <div class="col-md-6">
                        <label class="form-label d-block">Status</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" {{ $client->is_active ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <button class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Save Changes
                    </button>
                    <a href="{{ route('admin.clients.show', $client) }}" class="btn btn-outline-secondary ms-2">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection