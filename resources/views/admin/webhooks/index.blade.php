@extends('layouts.admin')

@section('title', 'Webhook Configuration')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Webhook Configuration</h4>
    <form action="{{ route('admin.webhooks.toggle-status') }}" method="POST" class="d-inline">
        @csrf
        @php $enabled = \App\Models\Setting::get('enable_lead_webhook', true); @endphp
        <button type="submit" class="btn {{ $enabled ? 'btn-success' : 'btn-outline-danger' }}">
            <i class="fas fa-power-off me-2"></i>
            Webhooks: {{ $enabled ? 'Enabled' : 'Disabled' }}
        </button>
    </form>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-4">
    {{-- Meta Configuration --}}
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fab fa-facebook me-2"></i>Meta (Facebook / Instagram)
                </h5>
            </div>
            <div class="card-body">
                {{-- Callback URL --}}
                <div class="mb-4">
                    <label class="form-label small text-muted">Callback URL (POST & GET)</label>
                    <div class="input-group">
                        <input type="text" class="form-control form-control-sm font-monospace" 
                               value="{{ $webhookUrls['meta_callback'] }}" readonly id="metaUrl">
                        <button class="btn btn-outline-secondary btn-sm" type="button" onclick="copyUrl('metaUrl')">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                </div>

                {{-- Settings Form --}}
                <form action="{{ route('admin.webhooks.update-meta') }}" method="POST" class="mb-4">
                    @csrf
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Verify Token</label>
                            <div class="input-group">
                                <input type="text" class="form-control font-monospace" name="meta_webhook_verify_token" 
                                       id="metaVerifyToken" value="{{ $settings['meta_webhook_verify_token'] ?? '' }}"
                                       placeholder="Enter or generate a token">
                                <button class="btn btn-outline-secondary" type="button" onclick="generateToken('metaVerifyToken')">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                            </div>
                            <small class="text-muted">Use this token when configuring the webhook in Meta Developer Console</small>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Page Access Token</label>
                            <input type="password" class="form-control" name="meta_access_token" 
                                   placeholder="{{ $settings['meta_access_token'] ? '••••••••••••••••' : 'Enter access token' }}">
                            <small class="text-muted">Required to fetch lead details from Meta API</small>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Save Meta Settings
                            </button>
                        </div>
                    </div>
                </form>

                <hr>

                {{-- Page Mapping --}}
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0">Page Mapping (Page ID → Client)</h6>
                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addMetaMappingModal">
                        <i class="fas fa-plus me-1"></i>Add
                    </button>
                </div>

                @php $metaMapping = $settings['meta_page_mapping'] ?? []; @endphp
                
                @if(count($metaMapping) > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Page ID</th>
                                    <th>Page Name</th>
                                    <th>Client</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($metaMapping as $pageId => $data)
                                    @php
                                        $clientId = is_array($data) ? $data['client_id'] : $data;
                                        $pageName = is_array($data) ? ($data['page_name'] ?? '-') : '-';
                                        $client = $clients->firstWhere('id', $clientId);
                                    @endphp
                                    <tr>
                                        <td class="font-monospace small">{{ $pageId }}</td>
                                        <td>{{ $pageName }}</td>
                                        <td>
                                            @if($client)
                                                <span class="badge bg-light text-dark">
                                                    {{ $client->company_name ?? $client->name }}
                                                </span>
                                            @else
                                                <span class="text-danger">Not found</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <form action="{{ route('admin.webhooks.delete-meta-mapping') }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="page_id" value="{{ $pageId }}">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                        onclick="return confirm('Delete this mapping?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-link fa-2x mb-2"></i>
                        <p class="mb-0">No page mappings configured</p>
                        <small>Add a mapping to route leads to the correct client</small>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Google Configuration --}}
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0">
                    <i class="fab fa-google me-2"></i>Google Ads
                </h5>
            </div>
            <div class="card-body">
                {{-- Callback URL --}}
                <div class="mb-4">
                    <label class="form-label small text-muted">Callback URL (POST)</label>
                    <div class="input-group">
                        <input type="text" class="form-control form-control-sm font-monospace" 
                               value="{{ $webhookUrls['google'] }}" readonly id="googleUrl">
                        <button class="btn btn-outline-secondary btn-sm" type="button" onclick="copyUrl('googleUrl')">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                </div>

                {{-- Settings Form --}}
                <form action="{{ route('admin.webhooks.update-google') }}" method="POST" class="mb-4">
                    @csrf
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Webhook Secret</label>
                            <div class="input-group">
                                <input type="text" class="form-control font-monospace" name="google_webhook_secret" 
                                       id="googleSecret" value="{{ $settings['google_webhook_secret'] ?? '' }}"
                                       placeholder="Enter or generate a secret">
                                <button class="btn btn-outline-secondary" type="button" onclick="generateToken('googleSecret')">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                            </div>
                            <small class="text-muted">Include as X-Webhook-Secret header in requests</small>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-save me-2"></i>Save Google Settings
                            </button>
                        </div>
                    </div>
                </form>

                <hr>

                {{-- Account Mapping --}}
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0">Account Mapping (Customer ID → Client)</h6>
                    <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#addGoogleMappingModal">
                        <i class="fas fa-plus me-1"></i>Add
                    </button>
                </div>

                @php $googleMapping = $settings['google_account_mapping'] ?? []; @endphp
                
                @if(count($googleMapping) > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Customer ID</th>
                                    <th>Account Name</th>
                                    <th>Client</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($googleMapping as $customerId => $data)
                                    @php
                                        $clientId = is_array($data) ? $data['client_id'] : $data;
                                        $accountName = is_array($data) ? ($data['account_name'] ?? '-') : '-';
                                        $client = $clients->firstWhere('id', $clientId);
                                    @endphp
                                    <tr>
                                        <td class="font-monospace small">{{ $customerId }}</td>
                                        <td>{{ $accountName }}</td>
                                        <td>
                                            @if($client)
                                                <span class="badge bg-light text-dark">
                                                    {{ $client->company_name ?? $client->name }}
                                                </span>
                                            @else
                                                <span class="text-danger">Not found</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <form action="{{ route('admin.webhooks.delete-google-mapping') }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="customer_id" value="{{ $customerId }}">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                        onclick="return confirm('Delete this mapping?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-link fa-2x mb-2"></i>
                        <p class="mb-0">No account mappings configured</p>
                        <small>Add a mapping to route leads to the correct client</small>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Razorpay Section --}}
<div class="card mt-4">
    <div class="card-header bg-info text-white">
        <h5 class="mb-0">
            <i class="fas fa-credit-card me-2"></i>Razorpay
        </h5>
    </div>
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-8">
                <label class="form-label small text-muted">Webhook URL</label>
                <div class="input-group">
                    <input type="text" class="form-control font-monospace" 
                           value="{{ $webhookUrls['razorpay'] }}" readonly id="razorpayUrl">
                    <button class="btn btn-outline-secondary" type="button" onclick="copyUrl('razorpayUrl')">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>
            </div>
            <div class="col-md-4">
                <p class="small text-muted mb-0 mt-3 mt-md-0">
                    Configure this URL in your Razorpay Dashboard under Settings → Webhooks. 
                    Select events: <code>payment.captured</code>, <code>payment.failed</code>
                </p>
            </div>
        </div>
    </div>
</div>

{{-- Recent Activity --}}
<div class="card mt-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Recent Webhook Activity</h5>
        <a href="{{ route('admin.webhooks.logs') }}" class="btn btn-sm btn-outline-primary">
            View All Logs
        </a>
    </div>
    <div class="card-body p-0">
        @if($recentLogs->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Source</th>
                            <th>Event</th>
                            <th>Status</th>
                            <th>IP</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentLogs as $log)
                            <tr>
                                <td class="small">{{ $log->created_at->diffForHumans() }}</td>
                                <td>
                                    @if($log->source === 'meta')
                                        <span class="badge bg-primary">Meta</span>
                                    @elseif($log->source === 'google')
                                        <span class="badge bg-danger">Google</span>
                                    @else
                                        <span class="badge bg-info">{{ ucfirst($log->source) }}</span>
                                    @endif
                                </td>
                                <td class="small">{{ $log->event_type ?? '-' }}</td>
                                <td>
                                    @if($log->status === 'processed')
                                        <span class="badge bg-success">Processed</span>
                                    @elseif($log->status === 'failed')
                                        <span class="badge bg-danger">Failed</span>
                                    @else
                                        <span class="badge bg-secondary">{{ ucfirst($log->status) }}</span>
                                    @endif
                                </td>
                                <td class="font-monospace small">{{ $log->ip_address }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-4 text-muted">
                <i class="fas fa-inbox fa-2x mb-2"></i>
                <p class="mb-0">No webhook activity yet</p>
            </div>
        @endif
    </div>
</div>

{{-- Add Meta Mapping Modal --}}
<div class="modal fade" id="addMetaMappingModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.webhooks.add-meta-mapping') }}" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fab fa-facebook me-2"></i>Add Meta Page Mapping
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Facebook Page ID <span class="text-danger">*</span></label>
                        <input type="text" class="form-control font-monospace" name="page_id" 
                               placeholder="123456789012345" required>
                        <small class="text-muted">
                            Find this in Facebook Page Settings → Page Info → Page ID
                        </small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Page Name <span class="text-muted">(for reference)</span></label>
                        <input type="text" class="form-control" name="page_name" 
                               placeholder="My Business Page">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Assign to Client <span class="text-danger">*</span></label>
                        <select class="form-select" name="client_id" required>
                            <option value="">Select a client</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}">
                                    {{ $client->company_name ?? $client->name }} ({{ $client->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="alert alert-info small mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        When a lead comes from this Facebook page, it will be automatically assigned to the selected client.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Add Mapping
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Add Google Mapping Modal --}}
<div class="modal fade" id="addGoogleMappingModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.webhooks.add-google-mapping') }}" method="POST">
                @csrf
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="fab fa-google me-2"></i>Add Google Account Mapping
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Google Ads Customer ID <span class="text-danger">*</span></label>
                        <input type="text" class="form-control font-monospace" name="customer_id" 
                               placeholder="123-456-7890" required>
                        <small class="text-muted">
                            Find this in Google Ads → Settings → Account Info (format: XXX-XXX-XXXX)
                        </small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Account Name <span class="text-muted">(for reference)</span></label>
                        <input type="text" class="form-control" name="account_name" 
                               placeholder="My Google Ads Account">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Assign to Client <span class="text-danger">*</span></label>
                        <select class="form-select" name="client_id" required>
                            <option value="">Select a client</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}">
                                    {{ $client->company_name ?? $client->name }} ({{ $client->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="alert alert-info small mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        When a lead comes from this Google Ads account, it will be automatically assigned to the selected client.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-plus me-2"></i>Add Mapping
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function copyUrl(inputId) {
    const input = document.getElementById(inputId);
    input.select();
    document.execCommand('copy');
    
    // Show feedback
    const btn = input.nextElementSibling;
    const originalHtml = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-check"></i>';
    btn.classList.remove('btn-outline-secondary');
    btn.classList.add('btn-success');
    
    setTimeout(() => {
        btn.innerHTML = originalHtml;
        btn.classList.remove('btn-success');
        btn.classList.add('btn-outline-secondary');
    }, 2000);
}

async function generateToken(inputId) {
    try {
        const response = await fetch('{{ route("admin.webhooks.generate-token") }}');
        const data = await response.json();
        document.getElementById(inputId).value = data.token;
    } catch (error) {
        alert('Failed to generate token');
    }
}
</script>
@endpush