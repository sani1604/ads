@extends('layouts.admin')

@section('title', 'Upload Creative')

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Upload Creative (On Behalf of Client)</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.creatives.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row g-3">
                    {{-- Client --}}
                    <div class="col-md-6">
                        <label class="form-label">Client</label>
                        <select name="user_id" class="form-select select2" required>
                            <option value="">Select client</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}" 
                                    {{ isset($selectedClient) && $selectedClient && $selectedClient->id == $client->id ? 'selected' : '' }}>
                                    {{ $client->company_name ?? $client->name }} ({{ $client->email }})
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted small">
                            Only active clients with subscriptions are listed.
                        </small>
                    </div>

                    {{-- Service Category --}}
                    <div class="col-md-6">
                        <x-form.select 
                            name="service_category_id"
                            label="Service Category"
                            :options="$categories->pluck('name','id')->toArray()"
                            placeholder="Select service"
                        />
                    </div>

                    {{-- Basic info --}}
                    <div class="col-md-6">
                        <x-form.input 
                            name="title"
                            label="Title"
                            :required="true"
                            placeholder="e.g. Diwali Offer Ad"
                        />
                    </div>
                    <div class="col-md-6">
                        <x-form.select 
                            name="platform"
                            label="Platform"
                            :required="true"
                            :options="[
                                'facebook'  => 'Facebook',
                                'instagram' => 'Instagram',
                                'google'    => 'Google',
                                'linkedin'  => 'LinkedIn',
                                'twitter'   => 'Twitter',
                                'youtube'   => 'YouTube',
                                'all'       => 'All / Generic',
                            ]"
                            :selected="'facebook'"
                        />
                    </div>

                    <div class="col-md-6">
                        <x-form.select 
                            name="type"
                            label="Creative Type"
                            :required="true"
                            :options="[
                                'image'    => 'Image',
                                'video'    => 'Video',
                                'carousel' => 'Carousel',
                                'story'    => 'Story',
                                'reel'     => 'Reel',
                                'document' => 'Document / PDF',
                            ]"
                            :selected="'image'"
                        />
                    </div>
                    <div class="col-md-6">
                        <x-form.input 
                            name="scheduled_date"
                            label="Scheduled Date (optional)"
                            type="date"
                        />
                    </div>

                    <div class="col-12">
                        <x-form.textarea 
                            name="description"
                            label="Description (internal)"
                            rows="2"
                            placeholder="Any notes for internal use..."
                        />
                    </div>

                    {{-- Ad copy / CTA --}}
                    <div class="col-12">
                        <x-form.textarea 
                            name="ad_copy"
                            label="Ad Copy / Caption"
                            rows="3"
                            placeholder="Text that will be used in the ad..."
                        />
                    </div>
                    <div class="col-md-6">
                        <x-form.input 
                            name="cta_text"
                            label="CTA Text"
                            placeholder="e.g. Learn More, Book Now"
                        />
                    </div>
                    <div class="col-md-6">
                        <x-form.input 
                            name="landing_url"
                            label="Landing URL"
                            type="url"
                            placeholder="https://example.com/offer"
                        />
                    </div>

                    {{-- Files --}}
                    <div class="col-12">
                        <x-form.file 
                            name="files"
                            label="Creative Files"
                            :multiple="true"
                            :required="true"
                            accept=".jpg,.jpeg,.png,.gif,.mp4,.mov,.avi,.pdf"
                            :preview="true"
                            help="Upload images/videos/PDFs (max 10 files, 50MB each)."
                        />
                    </div>
                </div>

                <div class="mt-4 d-flex justify-content-between">
                    <a href="{{ route('admin.creatives.index') }}" class="btn btn-outline-secondary">
                        Cancel
                    </a>
                    <button class="btn btn-primary">
                        <i class="fas fa-upload me-1"></i>Save Creative
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection