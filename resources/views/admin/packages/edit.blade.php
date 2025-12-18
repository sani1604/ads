{{-- resources/views/admin/packages/edit.blade.php --}}
@extends('layouts.admin')

@section('title', 'Edit Package')

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Edit Package</h5>
        </div>
        <div class="card-body">
            <form id="packageForm" action="{{ route('admin.packages.update', $package) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row g-3">

                    <div class="col-md-4">
                        <x-form.select 
                            name="service_category_id"
                            label="Service Category"
                            :required="true"
                            :options="$categories->pluck('name','id')->toArray()"
                            :selected="old('service_category_id', $package->service_category_id)"
                        />
                    </div>

                    <div class="col-md-4">
                        <x-form.select 
                            name="industry_id"
                            label="Industry (Optional)"
                            :options="$industries->pluck('name','id')->toArray()"
                            :selected="old('industry_id', $package->industry_id)"
                            placeholder="All industries"
                        />
                    </div>

                    <div class="col-md-4">
                        <x-form.input 
                            name="name"
                            label="Package Name"
                            :required="true"
                            :value="old('name', $package->name)"
                        />
                    </div>

                    <div class="col-md-6">
                        <x-form.input 
                            name="slug"
                            label="Slug"
                            :required="true"
                            :value="old('slug', $package->slug)"
                        />
                    </div>

                    <div class="col-md-6">
                        <x-form.input 
                            name="short_description"
                            label="Short Description"
                            :value="old('short_description', $package->short_description)"
                        />
                    </div>

                    <div class="col-12">
                        <x-form.textarea 
                            name="description"
                            label="Full Description"
                            rows="3"
                            :value="old('description', $package->description)"
                        />
                    </div>

                    <div class="col-md-4">
                        <x-form.input 
                            name="price"
                            label="Price (₹)"
                            type="number"
                            :required="true"
                            :value="old('price', $package->price)"
                        />
                    </div>

                    <div class="col-md-4">
                        <x-form.input 
                            name="original_price"
                            label="Original Price (₹)"
                            type="number"
                            :value="old('original_price', $package->original_price)"
                        />
                    </div>

                    <div class="col-md-4">
                        <x-form.select 
                            name="billing_cycle"
                            label="Billing Cycle"
                            :required="true"
                            :options="['monthly'=>'Monthly','quarterly'=>'Quarterly','yearly'=>'Yearly']"
                            :selected="old('billing_cycle', $package->billing_cycle)"
                        />
                    </div>

                    <div class="col-md-4">
                        <x-form.input 
                            name="billing_cycle_days"
                            label="Billing Days"
                            type="number"
                            :required="true"
                            :value="old('billing_cycle_days', $package->billing_cycle_days)"
                        />
                    </div>

                    <div class="col-md-4">
                        <x-form.input 
                            name="max_creatives_per_month"
                            label="Max Creatives / Month"
                            type="number"
                            :required="true"
                            :value="old('max_creatives_per_month', $package->max_creatives_per_month)"
                        />
                    </div>

                    <div class="col-md-4">
                        <x-form.input 
                            name="max_revisions"
                            label="Max Revisions"
                            type="number"
                            :required="true"
                            :value="old('max_revisions', $package->max_revisions)"
                        />
                    </div>

                    {{-- FEATURES --}}
                    <div class="col-md-6">
                        <label class="form-label">Features (one per line)</label>

                        @php
                            // Support: array, JSON string, or null
                            $featuresArr = [];
                            if (old('features') && is_array(old('features'))) {
                                $featuresArr = old('features');
                            } else {
                                $raw = $package->features ?? null;
                                if (is_array($raw)) {
                                    $featuresArr = $raw;
                                } elseif (is_string($raw)) {
                                    $decoded = @json_decode($raw, true);
                                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                        $featuresArr = $decoded;
                                    } else {
                                        // If it's a single-string that contains line breaks, split
                                        $featuresArr = preg_split('/\r\n|\r|\n/', $raw);
                                    }
                                }
                            }
                            // trim and remove empty
                            $featuresArr = array_values(array_filter(array_map('trim', (array) $featuresArr), fn($v) => $v !== ''));
                        @endphp

                        <textarea name="features" rows="6" class="form-control" placeholder="One feature per line...">{{
                            old('features_text', implode("\n", $featuresArr))
                        }}</textarea>
                        <small class="text-muted d-block mt-1">Enter one feature per line. On submit these will be converted into <code>features[]</code> inputs.</small>
                    </div>

                    {{-- DELIVERABLES --}}
                    <div class="col-md-6">
                        <label class="form-label">Deliverables (one per line)</label>

                        @php
                            $delArr = [];
                            if (old('deliverables') && is_array(old('deliverables'))) {
                                $delArr = old('deliverables');
                            } else {
                                $rawD = $package->deliverables ?? null;
                                if (is_array($rawD)) {
                                    $delArr = $rawD;
                                } elseif (is_string($rawD)) {
                                    $decodedD = @json_decode($rawD, true);
                                    if (json_last_error() === JSON_ERROR_NONE && is_array($decodedD)) {
                                        $delArr = $decodedD;
                                    } else {
                                        $delArr = preg_split('/\r\n|\r|\n/', $rawD);
                                    }
                                }
                            }
                            $delArr = array_values(array_filter(array_map('trim', (array) $delArr), fn($v) => $v !== ''));
                        @endphp

                        <textarea name="deliverables" rows="6" class="form-control" placeholder="One deliverable per line...">{{
                            old('deliverables_text', implode("\n", $delArr))
                        }}</textarea>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label d-block">Featured</label>
                        {{-- ensure a value is always submitted --}}
                        <input type="hidden" name="is_featured" value="0">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_featured" id="is_featured" value="1"
                                   {{ old('is_featured', $package->is_featured) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_featured">Mark as featured</label>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label d-block">Status</label>
                        <input type="hidden" name="is_active" value="0">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"
                                   {{ old('is_active', $package->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <x-form.input 
                            name="sort_order"
                            label="Sort Order"
                            type="number"
                            :value="old('sort_order', $package->sort_order)"
                        />
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Save Changes
                    </button>
                    <a href="{{ route('admin.packages.index') }}" class="btn btn-outline-secondary ms-2">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- JS to convert textarea -> features[] & deliverables[] hidden inputs before submit (Blade-only fix) --}}
    <script>
        (function () {
            // Wait for DOM
            document.addEventListener('DOMContentLoaded', function () {
                const form = document.getElementById('packageForm');
                if (!form) return;

                form.addEventListener('submit', function (e) {
                    // Remove any previously added hidden inputs to avoid duplicates (in case of double submit)
                    Array.from(form.querySelectorAll('input[name="features[]"], input[name="deliverables[]"]')).forEach(i => i.remove());

                    // FEATURES
                    const featuresTextarea = form.querySelector('textarea[name="features"]');
                    if (featuresTextarea) {
                        const featuresText = featuresTextarea.value || '';
                        const featuresLines = featuresText.split(/\r\n|\r|\n/).map(s => s.trim()).filter(s => s.length > 0);

                        featuresLines.forEach(line => {
                            const inp = document.createElement('input');
                            inp.type = 'hidden';
                            inp.name = 'features[]';
                            inp.value = line;
                            form.appendChild(inp);
                        });
                    }

                    // DELIVERABLES
                    const delTextarea = form.querySelector('textarea[name="deliverables"]');
                    if (delTextarea) {
                        const delText = delTextarea.value || '';
                        const delLines = delText.split(/\r\n|\r|\n/).map(s => s.trim()).filter(s => s.length > 0);

                        delLines.forEach(line => {
                            const inp = document.createElement('input');
                            inp.type = 'hidden';
                            inp.name = 'deliverables[]';
                            inp.value = line;
                            form.appendChild(inp);
                        });
                    }

                    // Note: hidden inputs for is_active/is_featured already present in the form (so validation gets true/false).
                    // Allow form to submit normally afterwards.
                });
            });
        })();
    </script>
@endsection
