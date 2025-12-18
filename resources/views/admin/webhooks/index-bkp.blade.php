{{-- resources/views/admin/webhooks/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Webhooks')

@section('content')
    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Webhook Configuration</h5>
            <form action="{{ route('admin.webhooks.toggle-status') }}" method="POST">
                @csrf
                @php $enabled = \App\Models\Setting::get('enable_lead_webhook', true); @endphp
                <button class="btn btn-sm {{ $enabled ? 'btn-success' : 'btn-outline-secondary' }}">
                    <i class="fas fa-plug me-1"></i>{{ $enabled ? 'Enabled' : 'Disabled' }}
                </button>
            </form>
        </div>
        <div class="card-body">
            <h6>Meta (Facebook / Instagram)</h6>
            <p class="small mb-1">Callback URL (POST & GET verify): <code>{{ $webhookUrls['meta_callback'] }}</code></p>
            <p class="small mb-1">Verify Token: <code>{{ $settings['meta_webhook_verify_token'] ?? 'not set' }}</code></p>
            <form action="{{ route('admin.webhooks.update-meta') }}" method="POST" class="row g-3 mb-3">
                @csrf
                <div class="col-md-6">
                    <x-form.input 
                        name="meta_webhook_verify_token"
                        label="Verify Token"
                        :value="$settings['meta_webhook_verify_token'] ?? ''"
                    />
                </div>
                <div class="col-md-6">
                    <x-form.input 
                        name="meta_access_token"
                        label="Meta Access Token"
                        type="password"
                    />
                </div>
                <div class="col-12">
                    <label class="form-label">Page Mapping (Page ID → Client)</label>
                    <div id="pageMapping">
                        @php $mapping = $settings['meta_page_mapping'] ?? []; @endphp
                        @foreach($mapping as $pageId => $clientId)
                            <div class="row g-2 mb-2">
                                <div class="col-md-5">
                                    <input type="text" name="meta_page_mapping[][page_id]" class="form-control form-control-sm"
                                           value="{{ $pageId }}" placeholder="Page ID">
                                </div>
                                <div class="col-md-5">
                                    <select name="meta_page_mapping[][client_id]" class="form-select form-select-sm">
                                        <option value="">Select client</option>
                                        @foreach(\App\Models\User::clients()->orderBy('name')->get() as $c)
                                            <option value="{{ $c->id }}" {{ $clientId == $c->id ? 'selected' : '' }}>
                                                {{ $c->company_name ?? $c->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        @endforeach
                        {{-- Empty template row can be added by JS if needed --}}
                    </div>
                </div>
                <div class="col-12">
                    <button class="btn btn-primary btn-sm"><i class="fas fa-save me-1"></i>Save Meta Settings</button>
                </div>
            </form>

            <hr>

            <h6>Google Ads</h6>
            <p class="small mb-1">Callback URL (POST): <code>{{ $webhookUrls['google'] }}</code></p>
            <form action="{{ route('admin.webhooks.update-google') }}" method="POST" class="row g-3">
                @csrf
                <div class="col-md-6">
                    <x-form.input 
                        name="google_webhook_secret"
                        label="Webhook Secret"
                        :value="$settings['google_webhook_secret'] ?? ''"
                    />
                </div>
                <div class="col-12">
                    <label class="form-label">Account Mapping (Customer ID → Client)</label>
                    @php $gmapping = $settings['google_account_mapping'] ?? []; @endphp
                    @foreach($gmapping as $customerId => $clientId)
                        <div class="row g-2 mb-2">
                            <div class="col-md-5">
                                <input type="text" name="google_account_mapping[][customer_id]" class="form-control form-control-sm"
                                       value="{{ $customerId }}" placeholder="Customer ID">
                            </div>
                            <div class="col-md-5">
                                <select name="google_account_mapping[][client_id]" class="form-select form-select-sm">
                                    <option value="">Select client</option>
                                    @foreach(\App\Models\User::clients()->orderBy('name')->get() as $c)
                                        <option value="{{ $c->id }}" {{ $clientId == $c->id ? 'selected' : '' }}>
                                            {{ $c->company_name ?? $c->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="col-12">
                    <button class="btn btn-primary btn-sm"><i class="fas fa-save me-1"></i>Save Google Settings</button>
                </div>
            </form>

            <hr>

            <h6>Razorpay</h6>
            <p class="small mb-0">Webhook URL: <code>{{ $webhookUrls['razorpay'] }}</code></p>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header d-flex justify-content-between">
            <h6 class="mb-0">Webhook Activity (from Activity Logs)</h6>
            <a href="{{ route('admin.webhooks.logs') }}" class="btn btn-outline-secondary btn-sm">View Logs</a>
        </div>
        <div class="card-body small text-muted">
            <p class="mb-0">Use this section to debug webhook delivery from Meta/Google/Razorpay. Ensure tokens are configured correctly.</p>
        </div>
    </div>
@endsection