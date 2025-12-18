{{-- resources/views/admin/campaign-reports/import.blade.php --}}
@extends('layouts.admin')

@section('title', 'Import Campaign Reports')

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Import Campaign Reports from CSV</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.campaign-reports.import') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Client</label>
                    <select name="user_id" class="form-select select2" required>
                        <option value="">Select client</option>
                        @foreach($clients as $c)
                            <option value="{{ $c->id }}">{{ $c->company_name ?? $c->name }}</option>
                        @endforeach
                    </select>
                </div>

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

                <x-form.file 
                    name="file"
                    label="CSV File"
                    accept=".csv,.txt"
                    :required="true"
                    help="Expected columns: Date, Campaign Name, Impressions, Clicks, Leads, Spend"
                />

                <button class="btn btn-primary">
                    <i class="fas fa-file-import me-1"></i>Import
                </button>
                <a href="{{ route('admin.campaign-reports.index') }}" class="btn btn-outline-secondary ms-2">
                    Cancel
                </a>
            </form>
        </div>
    </div>
@endsection