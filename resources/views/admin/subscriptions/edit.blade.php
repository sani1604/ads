{{-- resources/views/admin/subscriptions/edit.blade.php --}}
@extends('layouts.admin')

@section('title', 'Edit Subscription')

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Edit Subscription</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.subscriptions.update', $subscription) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select" required>
                            @foreach(['pending','active','paused','cancelled','expired'] as $s)
                                <option value="{{ $s }}" {{ $subscription->status == $s ? 'selected' : '' }}>
                                    {{ ucfirst($s) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <x-form.input 
                            name="end_date"
                            label="End Date"
                            type="date"
                            :required="true"
                            :value="$subscription->end_date->format('Y-m-d')"
                        />
                    </div>
                    <div class="col-md-4">
                        <x-form.input 
                            name="next_billing_date"
                            label="Next Billing Date"
                            type="date"
                            :required="true"
                            :value="$subscription->next_billing_date->format('Y-m-d')"
                        />
                    </div>
                    <div class="col-12">
                        <x-form.textarea 
                            name="notes"
                            label="Internal Notes"
                            rows="3"
                            :value="($subscription->meta_data['notes'] ?? '')"
                        />
                    </div>
                </div>

                <div class="mt-4">
                    <button class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Save Changes
                    </button>
                    <a href="{{ route('admin.subscriptions.show', $subscription) }}" class="btn btn-outline-secondary ms-2">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection