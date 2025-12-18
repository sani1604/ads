{{-- resources/views/admin/leads/edit.blade.php --}}
@extends('layouts.admin')

@section('title', 'Edit Lead')

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Edit Lead</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.leads.update', $lead) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-md-4">
                        <x-form.input 
                            name="name"
                            label="Lead Name"
                            :required="true"
                            :value="$lead->name"
                        />
                    </div>
                    <div class="col-md-4">
                        <x-form.input 
                            name="phone"
                            label="Phone"
                            :required="true"
                            :value="$lead->phone"
                        />
                    </div>
                    <div class="col-md-4">
                        <x-form.input 
                            name="email"
                            label="Email"
                            type="email"
                            :value="$lead->email"
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
                            :selected="$lead->source"
                        />
                    </div>
                    <div class="col-md-4">
                        <x-form.input 
                            name="campaign_name"
                            label="Campaign"
                            :value="$lead->campaign_name"
                        />
                    </div>
                    <div class="col-md-4">
                        <x-form.input 
                            name="ad_name"
                            label="Ad Name"
                            :value="$lead->ad_name"
                        />
                    </div>
                    <div class="col-md-4">
                        <x-form.select 
                            name="status"
                            label="Status"
                            :required="true"
                            :options="[
                                'new'       => 'New',
                                'contacted' => 'Contacted',
                                'qualified' => 'Qualified',
                                'converted' => 'Converted',
                                'lost'      => 'Lost',
                                'spam'      => 'Spam',
                            ]"
                            :selected="$lead->status"
                        />
                    </div>
                    <div class="col-md-4">
                        <x-form.select 
                            name="quality"
                            label="Quality"
                            :options="[
                                'hot'  => 'Hot',
                                'warm' => 'Warm',
                                'cold' => 'Cold',
                            ]"
                            :selected="$lead->quality"
                            placeholder="Not set"
                        />
                    </div>
                    <div class="col-md-6">
                        <x-form.input 
                            name="city"
                            label="City"
                            :value="$lead->city"
                        />
                    </div>
                    <div class="col-md-6">
                        <x-form.input 
                            name="state"
                            label="State"
                            :value="$lead->state"
                        />
                    </div>
                    <div class="col-12">
                        <x-form.textarea 
                            name="notes"
                            label="Notes"
                            rows="3"
                            :value="$lead->notes"
                        />
                    </div>
                </div>

                <div class="mt-4">
                    <button class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Save Changes
                    </button>
                    <a href="{{ route('admin.leads.show', $lead) }}" class="btn btn-outline-secondary ms-2">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection