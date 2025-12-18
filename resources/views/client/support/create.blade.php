{{-- resources/views/client/support/create.blade.php --}}
@extends('layouts.client')

@section('title', 'New Support Ticket')
@section('page-title', 'New Support Ticket')

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('client.support.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row g-3">
                    <div class="col-md-8">
                        <x-form.input 
                            name="subject" 
                            label="Subject" 
                            :required="true"
                            placeholder="Brief summary of your issue"
                        />

                        <x-form.select 
                            name="category"
                            label="Category"
                            :required="true"
                            :options="[
                                'billing'   => 'Billing & Invoices',
                                'technical' => 'Technical Issue',
                                'creative'  => 'Creatives / Ads',
                                'leads'     => 'Leads / Reporting',
                                'general'   => 'General Query',
                            ]"
                            placeholder="Select category"
                        />

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

                        <x-form.textarea 
                            name="message"
                            label="Describe your issue"
                            :required="true"
                            rows="6"
                            placeholder="Please share as much detail as possible..."
                        />

                        <x-form.file 
                            name="attachments"
                            label="Attachments (optional)"
                            :multiple="true"
                            accept=".jpg,.jpeg,.png,.pdf,.doc,.docx"
                            help="You can attach screenshots, PDFs, or documents (max 5 files, 5MB each)."
                        />

                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-1"></i> Submit Ticket
                            </button>
                            <a href="{{ route('client.support.index') }}" class="btn btn-outline-secondary ms-2">
                                Cancel
                            </a>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <h6 class="text-muted mb-2">Tips for faster resolution</h6>
                        <ul class="small text-muted mb-0">
                            <li class="mb-2">Attach screenshots or examples where possible.</li>
                            <li class="mb-2">Mention the campaign / ad name if the issue is campaign-specific.</li>
                            <li class="mb-2">For billing issues, mention invoice number or transaction ID.</li>
                        </ul>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection