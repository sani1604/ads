{{-- resources/views/admin/leads/import.blade.php --}}
@extends('layouts.admin')

@section('title', 'Import Leads')

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Import Leads from CSV</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.leads.import') }}" method="POST" enctype="multipart/form-data">
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

                <x-form.file 
                    name="file"
                    label="CSV File"
                    accept=".csv,.txt"
                    :required="true"
                    help="Columns: Name, Email, Phone, Source, Campaign (header row will be skipped)."
                />

                <button class="btn btn-primary">
                    <i class="fas fa-file-import me-1"></i>Import
                </button>
                <a href="{{ route('admin.leads.index') }}" class="btn btn-outline-secondary ms-2">
                    Cancel
                </a>
            </form>
        </div>
    </div>
@endsection