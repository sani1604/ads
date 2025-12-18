{{-- resources/views/admin/packages/create.blade.php --}}
@extends('layouts.admin')

@section('title', 'Add Package')

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Add New Package</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.packages.store') }}" method="POST">
                @csrf

                <div class="row g-3">
                    <div class="col-md-4">
                        <x-form.select 
                            name="service_category_id"
                            label="Service Category"
                            :required="true"
                            :options="$categories->pluck('name','id')->toArray()"
                            placeholder="Select category"
                        />
                    </div>
                    <div class="col-md-4">
                        <x-form.select 
                            name="industry_id"
                            label="Industry (Optional)"
                            :options="$industries->pluck('name','id')->toArray()"
                            placeholder="All industries"
                        />
                    </div>
                    <div class="col-md-4">
                        <x-form.input 
                            name="name"
                            label="Package Name"
                            :required="true"
                        />
                    </div>
                    <div class="col-md-6">
                        <x-form.input 
                            name="slug"
                            label="Slug"
                            :required="true"
                            help="Used in URLs, e.g. meta-ads-starter"
                        />
                    </div>
                    <div class="col-md-6">
                        <x-form.input 
                            name="short_description"
                            label="Short Description"
                        />
                    </div>
                    <div class="col-12">
                        <x-form.textarea 
                            name="description"
                            label="Full Description"
                            rows="3"
                        />
                    </div>

                    <div class="col-md-4">
                        <x-form.input 
                            name="price"
                            label="Price (₹)"
                            type="number"
                            :required="true"
                        />
                    </div>
                    <div class="col-md-4">
                        <x-form.input 
                            name="original_price"
                            label="Original Price (₹)"
                            type="number"
                            help="Optional – for showing discount"
                        />
                    </div>
                    <div class="col-md-4">
                        <x-form.select 
                            name="billing_cycle"
                            label="Billing Cycle"
                            :required="true"
                            :options="['monthly'=>'Monthly','quarterly'=>'Quarterly','yearly'=>'Yearly']"
                            :selected="'monthly'"
                        />
                    </div>
                    <div class="col-md-4">
                        <x-form.input 
                            name="billing_cycle_days"
                            label="Billing Days"
                            type="number"
                            :required="true"
                            :value="30"
                        />
                    </div>
                    <div class="col-md-4">
                        <x-form.input 
                            name="max_creatives_per_month"
                            label="Max Creatives / Month"
                            type="number"
                            :required="true"
                            :value="10"
                        />
                    </div>
                    <div class="col-md-4">
                        <x-form.input 
                            name="max_revisions"
                            label="Max Revisions"
                            type="number"
                            :required="true"
                            :value="3"
                        />
                    </div>

                    {{-- Features & Deliverables simple textareas --}}
                    <div class="col-md-6">
                        <label class="form-label">Features (one per line)</label>
                        <textarea name="features[]" rows="6" class="form-control"
                                  placeholder="2 Ad Campaigns&#10;8 Creatives / Month&#10;Weekly Reporting"></textarea>
                        <small class="text-muted">We’ll split by line breaks in controller if needed, or send as array from JS form.</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Deliverables (one per line)</label>
                        <textarea name="deliverables[]" rows="6" class="form-control"
                                  placeholder="Campaign Setup&#10;Ad Creative Design&#10;Monthly Strategy Call"></textarea>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label d-block">Featured</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_featured" id="is_featured">
                            <label class="form-check-label" for="is_featured">Mark as featured</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label d-block">Status</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" checked>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <x-form.input 
                            name="sort_order"
                            label="Sort Order"
                            type="number"
                            :value="0"
                        />
                    </div>
                </div>

                <div class="mt-4">
                    <button class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Create Package
                    </button>
                    <a href="{{ route('admin.packages.index') }}" class="btn btn-outline-secondary ms-2">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection