@extends('layouts.admin')

@section('title', 'Edit Creative')

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Edit Creative</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.creatives.update', $creative) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    {{-- Title & basic fields --}}
                    <div class="col-md-6">
                        <x-form.input 
                            name="title"
                            label="Title"
                            :required="true"
                            :value="$creative->title"
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
                            :selected="$creative->platform"
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
                            :selected="$creative->type"
                        />
                    </div>
                    <div class="col-md-6">
                        <x-form.select 
                            name="service_category_id"
                            label="Service Category"
                            :options="$categories->pluck('name','id')->toArray()"
                            :selected="$creative->service_category_id"
                            placeholder="Select service"
                        />
                    </div>
                    <div class="col-12">
                        <x-form.textarea 
                            name="description"
                            label="Description (internal)"
                            rows="2"
                            :value="$creative->description"
                        />
                    </div>
                    <div class="col-12">
                        <x-form.textarea 
                            name="ad_copy"
                            label="Ad Copy / Caption"
                            rows="3"
                            :value="$creative->ad_copy"
                        />
                    </div>
                    <div class="col-md-6">
                        <x-form.input 
                            name="cta_text"
                            label="CTA Text"
                            :value="$creative->cta_text"
                        />
                    </div>
                    <div class="col-md-6">
                        <x-form.input 
                            name="landing_url"
                            label="Landing URL"
                            type="url"
                            :value="$creative->landing_url"
                        />
                    </div>

                    {{-- Optionally upload more files --}}
                    <div class="col-12">
                        <x-form.file 
                            name="files"
                            label="Add More Files (optional)"
                            :multiple="true"
                            accept=".jpg,.jpeg,.png,.gif,.mp4,.mov,.avi,.pdf"
                            :preview="true"
                        />
                        <small class="text-muted">
                            Existing files stay as-is. Use delete actions on show page to remove them.
                        </small>
                    </div>
                </div>

                <div class="mt-4 d-flex justify-content-between">
                    <a href="{{ route('admin.creatives.show', $creative) }}" class="btn btn-outline-secondary">
                        Cancel
                    </a>
                    <button class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Update Creative
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection