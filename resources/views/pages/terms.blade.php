@extends('layouts.public')

@section('title', 'Terms of Service')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4 p-md-5">
                    <h1 class="h2 mb-4">Terms of Service</h1>
                    <p class="text-muted mb-4">Last updated: {{ now()->format('F d, Y') }}</p>

                    <div class="terms-content">
                        <section class="mb-5">
                            <h4>1. Acceptance of Terms</h4>
                            <p>By accessing and using {{ config('app.name') }} ("Service"), you acknowledge that you have read, understood, and agree to be bound by these Terms of Service ("Terms"). If you do not agree to these Terms, please do not use our Service.</p>
                            <p>We reserve the right to modify these Terms at any time. Your continued use of the Service following any changes indicates your acceptance of the new Terms.</p>
                        </section>

                        <section class="mb-5">
                            <h4>2. Description of Service</h4>
                            <p>{{ config('app.name') }} provides a lead management and digital marketing platform that includes:</p>
                            <ul>
                                <li>Lead capture and management from various advertising platforms (Facebook, Instagram, Google Ads)</li>
                                <li>Creative design and approval workflow management</li>
                                <li>Analytics and reporting dashboard</li>
                                <li>Wallet-based billing system</li>
                                <li>Client and campaign management tools</li>
                            </ul>
                        </section>

                        <section class="mb-5">
                            <h4>3. Account Registration</h4>
                            <p>To use our Service, you must:</p>
                            <ul>
                                <li>Be at least 18 years old or the legal age of majority in your jurisdiction</li>
                                <li>Provide accurate, current, and complete information during registration</li>
                                <li>Maintain and promptly update your account information</li>
                                <li>Keep your password secure and confidential</li>
                                <li>Notify us immediately of any unauthorized access to your account</li>
                            </ul>
                            <p>You are responsible for all activities that occur under your account.</p>
                        </section>

                        <section class="mb-5">
                            <h4>4. Subscription Plans and Payments</h4>
                            <h5>4.1 Billing</h5>
                            <p>Our Service operates on a subscription basis. By subscribing to a plan, you agree to pay the applicable fees as described during the checkout process.</p>
                            
                            <h5>4.2 Payment Methods</h5>
                            <p>We accept payments through Razorpay and other approved payment gateways. All payments are processed securely.</p>
                            
                            <h5>4.3 Wallet System</h5>
                            <p>Your account includes a wallet balance used for lead costs and additional services. You must maintain sufficient balance for uninterrupted service.</p>
                            
                            <h5>4.4 Taxes</h5>
                            <p>All prices are exclusive of applicable taxes (including GST at 18%) unless otherwise stated. You are responsible for paying all applicable taxes.</p>
                        </section>

                        <section class="mb-5">
                            <h4>5. Cancellation and Refunds</h4>
                            <p>Please refer to our <a href="{{ route('refund') }}">Refund Policy</a> for detailed information about cancellations and refunds.</p>
                            <ul>
                                <li>You may cancel your subscription at any time through your account settings</li>
                                <li>Cancellations take effect at the end of the current billing period</li>
                                <li>No refunds are provided for partial months of service</li>
                                <li>Wallet balances are non-refundable except as stated in our Refund Policy</li>
                            </ul>
                        </section>

                        <section class="mb-5">
                            <h4>6. Acceptable Use Policy</h4>
                            <p>You agree NOT to use the Service to:</p>
                            <ul>
                                <li>Violate any applicable laws, regulations, or third-party rights</li>
                                <li>Send spam, unsolicited messages, or misleading content</li>
                                <li>Upload malicious code, viruses, or harmful content</li>
                                <li>Attempt to gain unauthorized access to our systems</li>
                                <li>Interfere with or disrupt the Service or servers</li>
                                <li>Collect or harvest user data without consent</li>
                                <li>Impersonate any person or entity</li>
                                <li>Use the Service for any illegal or fraudulent activities</li>
                            </ul>
                        </section>

                        <section class="mb-5">
                            <h4>7. Intellectual Property</h4>
                            <p>All content, features, and functionality of the Service (including but not limited to text, graphics, logos, icons, images, software, and code) are owned by {{ config('app.name') }} and are protected by intellectual property laws.</p>
                            <p>You retain ownership of any content you upload to the Service. By uploading content, you grant us a non-exclusive license to use, store, and display such content solely for providing the Service.</p>
                        </section>

                        <section class="mb-5">
                            <h4>8. Data Privacy</h4>
                            <p>Your privacy is important to us. Please review our <a href="{{ route('privacy') }}">Privacy Policy</a> to understand how we collect, use, and protect your information.</p>
                        </section>

                        <section class="mb-5">
                            <h4>9. Third-Party Integrations</h4>
                            <p>Our Service integrates with third-party platforms including:</p>
                            <ul>
                                <li>Meta (Facebook/Instagram) - for lead generation</li>
                                <li>Google Ads - for lead generation</li>
                                <li>Razorpay - for payment processing</li>
                            </ul>
                            <p>Your use of these integrations is subject to the respective third-party terms of service.</p>
                        </section>

                        <section class="mb-5">
                            <h4>10. Disclaimer of Warranties</h4>
                            <p>THE SERVICE IS PROVIDED "AS IS" AND "AS AVAILABLE" WITHOUT WARRANTIES OF ANY KIND, EITHER EXPRESS OR IMPLIED. WE DO NOT WARRANT THAT:</p>
                            <ul>
                                <li>The Service will be uninterrupted or error-free</li>
                                <li>Defects will be corrected</li>
                                <li>The Service is free of viruses or harmful components</li>
                                <li>The results from using the Service will meet your requirements</li>
                            </ul>
                        </section>

                        <section class="mb-5">
                            <h4>11. Limitation of Liability</h4>
                            <p>TO THE MAXIMUM EXTENT PERMITTED BY LAW, {{ strtoupper(config('app.name')) }} SHALL NOT BE LIABLE FOR ANY INDIRECT, INCIDENTAL, SPECIAL, CONSEQUENTIAL, OR PUNITIVE DAMAGES, INCLUDING BUT NOT LIMITED TO LOSS OF PROFITS, DATA, OR BUSINESS OPPORTUNITIES.</p>
                            <p>Our total liability shall not exceed the amount you paid us in the twelve (12) months preceding the claim.</p>
                        </section>

                        <section class="mb-5">
                            <h4>12. Indemnification</h4>
                            <p>You agree to indemnify, defend, and hold harmless {{ config('app.name') }}, its officers, directors, employees, and agents from any claims, damages, losses, or expenses arising from:</p>
                            <ul>
                                <li>Your use of the Service</li>
                                <li>Your violation of these Terms</li>
                                <li>Your violation of any third-party rights</li>
                            </ul>
                        </section>

                        <section class="mb-5">
                            <h4>13. Termination</h4>
                            <p>We may suspend or terminate your account and access to the Service at any time, with or without cause, with or without notice. Upon termination:</p>
                            <ul>
                                <li>Your right to use the Service immediately ceases</li>
                                <li>We may delete your account data after a reasonable period</li>
                                <li>Any outstanding payments become immediately due</li>
                            </ul>
                        </section>

                        <section class="mb-5">
                            <h4>14. Governing Law</h4>
                            <p>These Terms shall be governed by and construed in accordance with the laws of India. Any disputes arising from these Terms or the Service shall be subject to the exclusive jurisdiction of the courts in [Your City], India.</p>
                        </section>

                        <section class="mb-5">
                            <h4>15. Changes to Terms</h4>
                            <p>We reserve the right to modify these Terms at any time. We will notify you of material changes by:</p>
                            <ul>
                                <li>Posting the updated Terms on our website</li>
                                <li>Sending an email notification to your registered email address</li>
                            </ul>
                            <p>Your continued use of the Service after changes constitutes acceptance of the modified Terms.</p>
                        </section>

                        <section class="mb-5">
                            <h4>16. Contact Information</h4>
                            <p>If you have any questions about these Terms, please contact us:</p>
                            <ul>
                                <li><strong>Email:</strong> legal@{{ str_replace(['http://', 'https://'], '', config('app.url')) }}</li>
                                <li><strong>Address:</strong> [Your Business Address]</li>
                                <li><strong>Phone:</strong> [Your Contact Number]</li>
                            </ul>
                        </section>
                    </div>

                    <hr class="my-4">

                    <div class="text-center">
                        <a href="{{ url()->previous() }}" class="btn btn-outline-primary">
                            <i class="fas fa-arrow-left me-2"></i>Go Back
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection