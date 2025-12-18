@extends('layouts.public')

@section('title', 'Contact Us')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="text-center mb-5">
                <h1 class="h2 mb-3">Get in Touch</h1>
                <p class="text-muted">Have questions? We'd love to hear from you.</p>
            </div>

            <div class="row g-4">
                {{-- Contact Info --}}
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body p-4">
                            <div class="mb-4">
                                <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 50px; height: 50px;">
                                    <i class="fas fa-envelope text-primary"></i>
                                </div>
                                <h5>Email Us</h5>
                                <p class="text-muted mb-1">General Inquiries</p>
                                <a href="mailto:hello@{{ str_replace(['http://', 'https://'], '', config('app.url')) }}">
                                    hello@{{ str_replace(['http://', 'https://'], '', config('app.url')) }}
                                </a>
                                <p class="text-muted mb-1 mt-2">Support</p>
                                <a href="mailto:support@{{ str_replace(['http://', 'https://'], '', config('app.url')) }}">
                                    support@{{ str_replace(['http://', 'https://'], '', config('app.url')) }}
                                </a>
                            </div>

                            <div class="mb-4">
                                <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 50px; height: 50px;">
                                    <i class="fas fa-phone text-success"></i>
                                </div>
                                <h5>Call Us</h5>
                                <p class="text-muted mb-1">Mon - Fri, 10 AM - 6 PM IST</p>
                                <a href="tel:+919876543210">+91 98765 43210</a>
                            </div>

                            <div>
                                <div class="bg-info bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 50px; height: 50px;">
                                    <i class="fas fa-map-marker-alt text-info"></i>
                                </div>
                                <h5>Visit Us</h5>
                                <p class="text-muted mb-0">
                                    123 Business Park<br>
                                    Tech Hub, Floor 5<br>
                                    Mumbai, Maharashtra 400001<br>
                                    India
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Contact Form --}}
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <h4 class="mb-4">Send us a Message</h4>

                            @if(session('success'))
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                                </div>
                            @endif

                            <form action="{{ route('contact.submit') ?? '#' }}" method="POST">
                                @csrf
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                               name="name" value="{{ old('name') }}" required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Email <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                               name="email" value="{{ old('email') }}" required>
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Phone</label>
                                        <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                                               name="phone" value="{{ old('phone') }}">
                                        @error('phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Subject <span class="text-danger">*</span></label>
                                        <select class="form-select @error('subject') is-invalid @enderror" name="subject" required>
                                            <option value="">Select a topic</option>
                                            <option value="general" {{ old('subject') == 'general' ? 'selected' : '' }}>General Inquiry</option>
                                            <option value="sales" {{ old('subject') == 'sales' ? 'selected' : '' }}>Sales & Pricing</option>
                                            <option value="support" {{ old('subject') == 'support' ? 'selected' : '' }}>Technical Support</option>
                                            <option value="billing" {{ old('subject') == 'billing' ? 'selected' : '' }}>Billing & Payments</option>
                                            <option value="partnership" {{ old('subject') == 'partnership' ? 'selected' : '' }}>Partnership</option>
                                            <option value="feedback" {{ old('subject') == 'feedback' ? 'selected' : '' }}>Feedback</option>
                                        </select>
                                        @error('subject')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Message <span class="text-danger">*</span></label>
                                        <textarea class="form-control @error('message') is-invalid @enderror" 
                                                  name="message" rows="5" required>{{ old('message') }}</textarea>
                                        @error('message')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary btn-lg">
                                            <i class="fas fa-paper-plane me-2"></i>Send Message
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            {{-- FAQ Section --}}
            <div class="mt-5">
                <h3 class="text-center mb-4">Frequently Asked Questions</h3>
                <div class="accordion" id="faqAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                How do I get started?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Simply sign up for a free account, complete the onboarding process, choose a subscription plan, and connect your advertising accounts. Our team will guide you through the setup.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                What payment methods do you accept?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                We accept all major credit/debit cards, UPI, net banking, and popular wallets through our secure payment partner Razorpay.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                Can I cancel my subscription anytime?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Yes, you can cancel your subscription at any time from your account settings. Your access will continue until the end of your current billing period.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                How does lead tracking work?
                            </button>
                        </h2>
                        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                We integrate directly with Meta (Facebook/Instagram) and Google Ads through official webhooks. When a lead is submitted on your ad, it's instantly captured and appears in your dashboard.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection