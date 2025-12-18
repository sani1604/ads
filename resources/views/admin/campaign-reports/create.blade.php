{{-- resources/views/admin/campaign-reports/create.blade.php --}}
@extends('layouts.admin')

@section('title', 'Add Campaign Report')

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Add Campaign Report (Manual)</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.campaign-reports.store') }}" method="POST">
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
                        <x-form.select 
                            name="platform"
                            label="Platform"
                            :required="true"
                            :options="[
                                'facebook'  => 'Facebook',
                                'instagram' => 'Instagram',
                                'google'    => 'Google',
                                'linkedin'  => 'LinkedIn',
                            ]"
                        />
                    </div>
                    <div class="col-md-4">
                        <x-form.input 
                            name="report_date"
                            label="Date"
                            type="date"
                            :required="true"
                            :value="now()->format('Y-m-d')"
                        />
                    </div>
                    <div class="col-md-6">
                        <x-form.input 
                            name="campaign_name"
                            label="Campaign Name"
                        />
                    </div>
                    <div class="col-md-6">
                        <x-form.input 
                            name="campaign_id"
                            label="Campaign ID (optional)"
                        />
                    </div>

                    {{-- Metrics --}}
                    <div class="col-md-4">
                        <x-form.input 
                            name="impressions"
                            label="Impressions"
                            type="number"
                            :required="true"
                            :value="0"
                        />
                    </div>
                    <div class="col-md-4">
                        <x-form.input 
                            name="clicks"
                            label="Clicks"
                            type="number"
                            :required="true"
                            :value="0"
                        />
                    </div>
                    <div class="col-md-4">
                        <x-form.input 
                            name="leads"
                            label="Leads"
                            type="number"
                            :value="0"
                        />
                    </div>
                    <div class="col-md-4">
                        <x-form.input 
                            name="spend"
                            label="Spend (₹)"
                            type="number"
                            step="0.01"
                            :required="true"
                            :value="0"
                        />
                    </div>
                    <div class="col-md-4">
                        <x-form.input 
                            name="video_views"
                            label="Video Views"
                            type="number"
                            :value="0"
                        />
                    </div>
                    <div class="col-md-4">
                        <x-form.input 
                            name="engagements"
                            label="Engagements"
                            type="number"
                            :value="0"
                        />
                    </div>
                </div>

                <div class="mt-4">
                    <button class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Add Report
                    </button>
                    <a href="{{ route('admin.campaign-reports.index') }}" class="btn btn-outline-secondary ms-2">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection