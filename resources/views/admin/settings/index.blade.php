{{-- resources/views/admin/settings/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Settings')

@section('content')
    <div class="card">
        <div class="card-header border-bottom-0 pb-0">
            <h5 class="mb-0">System Settings</h5>
        </div>
        <div class="card-body">
            <ul class="nav nav-tabs mb-3" id="settingsTabs">
                <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-general">General</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-payment">Payment</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-invoice">Invoice</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-email">Email</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-notification">Notification</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-social">Social</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-api">API/Webhooks</button></li>
            </ul>

            <div class="tab-content">
                {{-- General --}}
                <div class="tab-pane fade show active" id="tab-general">
                    <form action="{{ route('admin.settings.update-general') }}" method="POST" enctype="multipart/form-data" class="row g-3">
                        @csrf
                        <div class="col-md-6">
                            <x-form.input name="site_name" label="Site Name" :required="true" :value="old('site_name', \App\Models\Setting::get('site_name', config('app.name')))"/>
                        </div>
                        <div class="col-md-6">
                            <x-form.input name="site_tagline" label="Tagline" :value="\App\Models\Setting::get('site_tagline')"/>
                        </div>
                        <div class="col-md-6">
                            <x-form.input name="contact_email" label="Contact Email" type="email" :value="\App\Models\Setting::get('contact_email')"/>
                        </div>
                        <div class="col-md-6">
                            <x-form.input name="contact_phone" label="Contact Phone" :value="\App\Models\Setting::get('contact_phone')"/>
                        </div>
                        <div class="col-12">
                            <x-form.textarea name="address" label="Address" rows="2" :value="\App\Models\Setting::get('address')"/>
                        </div>
                        <div class="col-md-6">
                            <x-form.file name="logo" label="Logo" accept=".png,.jpg,.jpeg,.svg"/>
                        </div>
                        <div class="col-md-6">
                            <x-form.file name="favicon" label="Favicon" accept=".png,.ico"/>
                        </div>
                        <div class="col-12">
                            <button class="btn btn-primary"><i class="fas fa-save me-1"></i>Save General Settings</button>
                        </div>
                    </form>
                </div>

                {{-- Payment --}}
                <div class="tab-pane fade" id="tab-payment">
                    <form action="{{ route('admin.settings.update-payment') }}" method="POST" class="row g-3">
                        @csrf
                        <div class="col-md-3">
                            <x-form.input name="currency" label="Currency Code" :value="\App\Models\Setting::get('currency','INR')"/>
                        </div>
                        <div class="col-md-3">
                            <x-form.input name="currency_symbol" label="Currency Symbol" :value="\App\Models\Setting::get('currency_symbol','₹')"/>
                        </div>
                        <div class="col-md-3">
                            <x-form.input name="tax_rate" label="Tax Rate (%)" type="number" step="0.1" :value="\App\Models\Setting::get('tax_rate',18)"/>
                        </div>
                        <div class="col-md-3">
                            <x-form.input name="tax_name" label="Tax Name" :value="\App\Models\Setting::get('tax_name','GST')"/>
                        </div>
                        <div class="col-md-3">
                            <x-form.input name="min_wallet_recharge" label="Min Wallet Recharge" type="number" :value="\App\Models\Setting::get('min_wallet_recharge',5000)"/>
                        </div>
                        <div class="col-md-4">
                            <x-form.input name="razorpay_key_id" label="Razorpay Key ID" :value="\App\Models\Setting::get('razorpay_key_id')"/>
                        </div>
                        <div class="col-md-4">
                            <x-form.input name="razorpay_key_secret" label="Razorpay Key Secret" type="password"/>
                        </div>
                        <div class="col-12">
                            <button class="btn btn-primary"><i class="fas fa-save me-1"></i>Save Payment Settings</button>
                        </div>
                    </form>
                </div>

                {{-- Invoice --}}
                <div class="tab-pane fade" id="tab-invoice">
                    <form action="{{ route('admin.settings.update-invoice') }}" method="POST" class="row g-3">
                        @csrf
                        <div class="col-md-4">
                            <x-form.input name="invoice_prefix" label="Invoice Prefix" :value="\App\Models\Setting::get('invoice_prefix','INV-')"/>
                        </div>
                        <div class="col-md-8">
                            <x-form.input name="company_name" label="Company Name" :value="\App\Models\Setting::get('company_name')"/>
                        </div>
                        <div class="col-md-6">
                            <x-form.input name="company_gst" label="Company GST" :value="\App\Models\Setting::get('company_gst')"/>
                        </div>
                        <div class="col-md-6">
                            <x-form.input name="company_pan" label="Company PAN" :value="\App\Models\Setting::get('company_pan')"/>
                        </div>
                        <div class="col-12">
                            <x-form.textarea name="company_address" label="Billing Address" rows="2" :value="\App\Models\Setting::get('company_address')"/>
                        </div>
                        <div class="col-12">
                            <x-form.textarea name="invoice_footer" label="Invoice Footer Text" rows="2" :value="\App\Models\Setting::get('invoice_footer')"/>
                        </div>
                        <div class="col-12">
                            <x-form.textarea name="invoice_terms" label="Terms & Conditions" rows="3" :value="\App\Models\Setting::get('invoice_terms')"/>
                        </div>
                        <div class="col-12">
                            <button class="btn btn-primary"><i class="fas fa-save me-1"></i>Save Invoice Settings</button>
                        </div>
                    </form>
                </div>

                {{-- Email --}}
                <div class="tab-pane fade" id="tab-email">
                    <form action="{{ route('admin.settings.update-email') }}" method="POST" class="row g-3">
                        @csrf
                        <div class="col-md-6">
                            <x-form.input name="mail_from_name" label="From Name" :value="\App\Models\Setting::get('mail_from_name',config('mail.from.name'))"/>
                        </div>
                        <div class="col-md-6">
                            <x-form.input name="mail_from_address" label="From Email" :value="\App\Models\Setting::get('mail_from_address',config('mail.from.address'))"/>
                        </div>
                        <div class="col-md-4">
                            <x-form.input name="mail_host" label="SMTP Host" :value="\App\Models\Setting::get('mail_host',config('mail.host'))"/>
                        </div>
                        <div class="col-md-2">
                            <x-form.input name="mail_port" label="SMTP Port" type="number" :value="\App\Models\Setting::get('mail_port',config('mail.port'))"/>
                        </div>
                        <div class="col-md-3">
                            <x-form.input name="mail_username" label="SMTP Username" :value="\App\Models\Setting::get('mail_username',config('mail.username'))"/>
                        </div>
                        <div class="col-md-3">
                            <x-form.input name="mail_password" label="SMTP Password" type="password"/>
                        </div>
                        <div class="col-md-3">
                            <x-form.select 
                                name="mail_encryption"
                                label="Encryption"
                                :options="['tls'=>'TLS','ssl'=>'SSL','null'=>'None']"
                                :selected="\App\Models\Setting::get('mail_encryption',config('mail.encryption'))"
                            />
                        </div>
                        <div class="col-12 d-flex gap-2">
                            <button class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Save Email Settings
                            </button>
                            <form action="{{ route('admin.settings.test-email') }}" method="POST" class="d-flex align-items-center gap-2">
                                @csrf
                                <input type="email" name="test_email" class="form-control form-control-sm" placeholder="Test email" required>
                                <button class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-paper-plane me-1"></i>Send Test
                                </button>
                            </form>
                        </div>
                    </form>
                </div>

                {{-- Notification --}}
                <div class="tab-pane fade" id="tab-notification">
                    <form action="{{ route('admin.settings.update-notification') }}" method="POST" class="row g-3">
                        @csrf
                        <div class="col-md-4">
                            <label class="form-label d-block">New Lead Alerts</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="notify_new_lead" 
                                       {{ \App\Models\Setting::get('notify_new_lead',1) ? 'checked' : '' }}>
                                <label class="form-check-label">Enable</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label d-block">Creative Approval Alerts</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="notify_creative_approval" 
                                       {{ \App\Models\Setting::get('notify_creative_approval',1) ? 'checked' : '' }}>
                                <label class="form-check-label">Enable</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label d-block">Payment Alerts</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="notify_payment" 
                                       {{ \App\Models\Setting::get('notify_payment',1) ? 'checked' : '' }}>
                                <label class="form-check-label">Enable</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label d-block">Subscription Expiry Alerts</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="notify_subscription_expiry" 
                                       {{ \App\Models\Setting::get('notify_subscription_expiry',1) ? 'checked' : '' }}>
                                <label class="form-check-label">Enable</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <x-form.input 
                                name="expiry_reminder_days"
                                label="Days Before Expiry"
                                type="number"
                                :value="\App\Models\Setting::get('expiry_reminder_days',7)"
                            />
                        </div>
                        <div class="col-md-4">
                            <x-form.input 
                                name="admin_notification_email"
                                label="Admin Notification Email"
                                type="email"
                                :value="\App\Models\Setting::get('admin_notification_email')"
                            />
                        </div>
                        <div class="col-12">
                            <button class="btn btn-primary"><i class="fas fa-save me-1"></i>Save Notification Settings</button>
                        </div>
                    </form>
                </div>

                {{-- Social --}}
                <div class="tab-pane fade" id="tab-social">
                    <form action="{{ route('admin.settings.update-social') }}" method="POST" class="row g-3">
                        @csrf
                        <div class="col-md-6">
                            <x-form.input name="facebook_url" label="Facebook" :value="\App\Models\Setting::get('facebook_url')"/>
                        </div>
                        <div class="col-md-6">
                            <x-form.input name="instagram_url" label="Instagram" :value="\App\Models\Setting::get('instagram_url')"/>
                        </div>
                        <div class="col-md-6">
                            <x-form.input name="twitter_url" label="Twitter / X" :value="\App\Models\Setting::get('twitter_url')"/>
                        </div>
                        <div class="col-md-6">
                            <x-form.input name="linkedin_url" label="LinkedIn" :value="\App\Models\Setting::get('linkedin_url')"/>
                        </div>
                        <div class="col-md-6">
                            <x-form.input name="youtube_url" label="YouTube" :value="\App\Models\Setting::get('youtube_url')"/>
                        </div>
                        <div class="col-12">
                            <button class="btn btn-primary"><i class="fas fa-save me-1"></i>Save Social Links</button>
                        </div>
                    </form>
                </div>

                {{-- API / Webhooks simple section (for Settings only; Webhook management UI below) --}}
                <div class="tab-pane fade" id="tab-api">
                    <form action="{{ route('admin.settings.update-api') }}" method="POST" class="row g-3">
                        @csrf
                        <div class="col-md-6">
                            <x-form.input 
                                name="meta_webhook_verify_token"
                                label="Meta Webhook Verify Token"
                                :value="\App\Models\Setting::get('meta_webhook_verify_token')"
                            />
                        </div>
                        <div class="col-md-6">
                            <x-form.input 
                                name="google_webhook_secret"
                                label="Google Webhook Secret"
                                :value="\App\Models\Setting::get('google_webhook_secret')"
                            />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label d-block">Enable Lead Webhooks</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="enable_lead_webhook" 
                                       {{ \App\Models\Setting::get('enable_lead_webhook',1) ? 'checked' : '' }}>
                                <label class="form-check-label">Enable</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <button class="btn btn-primary"><i class="fas fa-save me-1"></i>Save API Settings</button>
                        </div>
                    </form>
                </div>
            </div>

            <hr class="mt-4">

            {{-- Maintenance --}}
            <div class="d-flex justify-content-between">
                <form action="{{ route('admin.settings.clear-cache') }}" method="POST">
                    @csrf
                    <button class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-broom me-1"></i>Clear Cache
                    </button>
                </form>
                <a href="{{ route('admin.settings.system-info') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-server me-1"></i>System Info
                </a>
                <a href="{{ route('admin.settings.backup') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-database me-1"></i>Download DB Backup
                </a>
            </div>
        </div>
    </div>
@endsection