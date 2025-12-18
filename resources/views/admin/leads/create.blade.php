{{-- resources/views/admin/leads/create.blade.php --}}
@extends('layouts.admin')

@section('title', 'Add Lead')

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Add New Lead</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.leads.store') }}" method="POST">
                @csrf

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Client</label>
                        <select name="user_id" class="form-select select2" required>
                            <option value="">Select client</option>
                            @foreach($clients as $c)
                                <option value="{{ $c->id }}" {{ request('client')==$c->id ? 'selected' : '' }}>
                                    {{ $c->company_name ?? $c->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <x-form.input 
                            name="name"
                            label="Lead Name"
                            :required="true"
                        />
                    </div>
                    <div class="col-md-4">
                        <x-form.input 
                            name="phone"
                            label="Phone"
                            :required="true"
                        />
                    </div>
                    <div class="col-md-4">
                        <x-form.input 
                            name="email"
                            label="Email"
                            type="email"
                        />
                    </div>
                    <div class="col-md-4">
                        <x-form.select 
                            name="source"
                            label="Source"
                            :required="true"
                            :options="[
                                'facebook'  => 'Facebook',
                                'instagram' => 'Instagram',
                                'google'    => 'Google',
                                'linkedin'  => 'LinkedIn',
                                'website'   => 'Website',
                                'manual'    => 'Manual',
                                'other'     => 'Other',
                            ]"
                        />
                    </div>
                    <div class="col-md-4">
                        <x-form.input 
                            name="campaign_name"
                            label="Campaign"
                        />
                    </div>
                    <div class="col-md-6">
                        <x-form.input 
                            name="city"
                            label="City"
                        />
                    </div>
                    <div class="col-md-6">
                        <x-form.input 
                            name="state"
                            label="State"
                        />
                    </div>
                    <div class="col-12">
                        <x-form.textarea 
                            name="notes"
                            label="Notes"
                            rows="3"
                        />
                    </div>
                </div>

                <div class="mt-4">
                    <button class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Create Lead
                    </button>
                    <a href="{{ route('admin.leads.index') }}" class="btn btn-outline-secondary ms-2">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection