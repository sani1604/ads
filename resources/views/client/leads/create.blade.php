@extends('layouts.client')

@section('title', 'Add Lead')
@section('page-title', 'Add Lead')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Add New Lead</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('client.leads.store') }}" method="POST">
            @csrf

            <div class="row g-3">
                <div class="col-md-6">
                    <x-form.input 
                        name="name"
                        label="Name"
                        :required="true"
                    />
                </div>
                <div class="col-md-6">
                    <x-form.input 
                        name="phone"
                        label="Phone"
                        :required="true"
                    />
                </div>
                <div class="col-md-6">
                    <x-form.input 
                        name="email"
                        label="Email"
                        type="email"
                    />
                </div>
                <div class="col-md-6">
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
                        :selected="'manual'"
                    />
                </div>
                <div class="col-md-6">
                    <x-form.input 
                        name="campaign_name"
                        label="Campaign (optional)"
                    />
                </div>
                <div class="col-md-6">
                    <x-form.select 
                        name="status"
                        label="Status"
                        :options="[
                            'new'       => 'New',
                            'contacted' => 'Contacted',
                            'qualified' => 'Qualified',
                            'converted' => 'Converted',
                            'lost'      => 'Lost',
                            'spam'      => 'Spam',
                        ]"
                        :selected="'new'"
                    />
                </div>
                <div class="col-md-6">
                    <x-form.select 
                        name="quality"
                        label="Quality"
                        :options="[
                            'hot'  => 'Hot',
                            'warm' => 'Warm',
                            'cold' => 'Cold',
                        ]"
                        placeholder="Not set"
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
                    <i class="fas fa-save me-1"></i>Save Lead
                </button>
                <a href="{{ route('client.leads.index') }}" class="btn btn-outline-secondary ms-2">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection