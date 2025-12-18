{{-- resources/views/admin/support-tickets/create.blade.php --}}
@extends('layouts.admin')

@section('title', 'Create Ticket')

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Create Support Ticket</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.support-tickets.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row g-3">
                    <div class="col-md-6">
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
                    <div class="col-md-6">
                        <x-form.input 
                            name="subject"
                            label="Subject"
                            :required="true"
                        />
                    </div>
                    <div class="col-md-4">
                        <x-form.select 
                            name="category"
                            label="Category"
                            :required="true"
                            :options="[
                                'billing'   => 'Billing',
                                'technical' => 'Technical',
                                'creative'  => 'Creative',
                                'leads'     => 'Leads / CRM',
                                'general'   => 'General',
                            ]"
                        />
                    </div>
                    <div class="col-md-4">
                        <x-form.select 
                            name="priority"
                            label="Priority"
                            :options="[
                                'low'    => 'Low',
                                'medium' => 'Medium',
                                'high'   => 'High',
                                'urgent' => 'Urgent',
                            ]"
                            :selected="'medium'"
                        />
                    </div>
                    <div class="col-md-12">
                        <x-form.textarea 
                            name="message"
                            label="Initial Message"
                            :required="true"
                            rows="5"
                        />
                    </div>
                    <div class="col-md-12">
                        <x-form.file 
                            name="attachments"
                            label="Attachments"
                            :multiple="true"
                            accept=".jpg,.jpeg,.png,.pdf,.doc,.docx"
                            help="Max 5 files, 5MB each."
                        />
                    </div>
                </div>

                <div class="mt-4">
                    <button class="btn btn-primary">
                        <i class="fas fa-paper-plane me-1"></i>Create Ticket
                    </button>
                    <a href="{{ route('admin.support-tickets.index') }}" class="btn btn-outline-secondary ms-2">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection