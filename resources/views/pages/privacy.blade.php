@extends('layouts.public')

@section('title', 'Privacy Policy')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4 p-md-5">
                    <h1 class="h2 mb-4">Privacy Policy</h1>
                    <p class="text-muted mb-4">Last updated: {{ now()->format('F d, Y') }}</p>

                    <div class="privacy-content">
                        <section class="mb-5">
                            <h4>1. Introduction</h4>
                            <p>{{ config('app.name') }} ("we," "our," or "us") is committed to protecting your privacy. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you use our service.</p>
                            <p>By using our Service, you consent to the data practices described in this policy.</p>
                        </section>

                        <section class="mb-5">
                            <h4>2. Information We Collect</h4>
                            
                            <h5>2.1 Personal Information</h5>
                            <p>We may collect personally identifiable information, including but not limited to:</p>
                            <ul>
                                <li><strong>Account Information:</strong> Name, email address, phone number, company name</li>
                                <li><strong>Billing Information:</strong> GST number, billing address, payment details (processed securely via Razorpay)</li>
                                <li><strong>Business Information:</strong> Industry, company size, advertising platforms used</li>
                            </ul>

                            <h5>2.2 Lead Data</h5>
                            <p>Through our integrations with Meta (Facebook/Instagram) and Google Ads, we collect lead information submitted by end-users through your advertising campaigns, including:</p>
                            <ul>
                                <li>Names and contact information</li>
                                <li>Form responses and custom fields</li>
                                <li>Campaign and ad metadata</li>
                            </ul>

                            <h5>2.3 Usage Data</h5>
                            <p>We automatically collect certain information when you access our Service:</p>
                            <ul>
                                <li>IP address and device information</li>
                                <li>Browser type and version</li>
                                <li>Pages visited and time spent</li>
                                <li>Referring website addresses</li>
                                <li>Click patterns and feature usage</li>
                            </ul>

                            <h5>2.4 Cookies and Tracking Technologies</h5>
                            <p>We use cookies and similar technologies to:</p>
                            <ul>
                                <li>Maintain your session and preferences</li>
                                <li>Analyze usage patterns</li>
                                <li>Improve our Service</li>
                            </ul>
                        </section>

                        <section class="mb-5">
                            <h4>3. How We Use Your Information</h4>
                            <p>We use the collected information for various purposes:</p>
                            <ul>
                                <li><strong>Service Delivery:</strong> To provide, maintain, and improve our Service</li>
                                <li><strong>Account Management:</strong> To manage your account and provide customer support</li>
                                <li><strong>Communication:</strong> To send transactional emails, updates, and promotional content (with your consent)</li>
                                <li><strong>Analytics:</strong> To understand how users interact with our Service</li>
                                <li><strong>Security:</strong> To detect and prevent fraud and abuse</li>
                                <li><strong>Legal Compliance:</strong> To comply with applicable laws and regulations</li>
                            </ul>
                        </section>

                        <section class="mb-5">
                            <h4>4. Data Sharing and Disclosure</h4>
                            <p>We may share your information in the following circumstances:</p>
                            
                            <h5>4.1 Service Providers</h5>
                            <p>We share data with trusted third-party service providers who assist us in operating our Service:</p>
                            <ul>
                                <li><strong>Razorpay:</strong> Payment processing</li>
                                <li><strong>AWS/Cloud Providers:</strong> Hosting and data storage</li>
                                <li><strong>Email Services:</strong> Transactional and marketing emails</li>
                            </ul>

                            <h5>4.2 Legal Requirements</h5>
                            <p>We may disclose your information if required by law or in response to valid legal requests.</p>

                            <h5>4.3 Business Transfers</h5>
                            <p>In case of a merger, acquisition, or sale of assets, your information may be transferred as part of the business transaction.</p>

                            <h5>4.4 With Your Consent</h5>
                            <p>We may share your information for other purposes with your explicit consent.</p>
                        </section>

                        <section class="mb-5">
                            <h4>5. Data Security</h4>
                            <p>We implement appropriate technical and organizational measures to protect your data:</p>
                            <ul>
                                <li>SSL/TLS encryption for data in transit</li>
                                <li>Encrypted storage for sensitive data</li>
                                <li>Regular security audits and vulnerability assessments</li>
                                <li>Access controls and authentication mechanisms</li>
                                <li>Employee training on data protection</li>
                            </ul>
                            <p>However, no method of transmission over the Internet is 100% secure. We cannot guarantee absolute security.</p>
                        </section>

                        <section class="mb-5">
                            <h4>6. Data Retention</h4>
                            <p>We retain your personal information for as long as:</p>
                            <ul>
                                <li>Your account is active</li>
                                <li>Necessary to provide our services</li>
                                <li>Required by law or for legitimate business purposes</li>
                            </ul>
                            <p>After account deletion, we may retain certain data for up to 90 days for backup purposes and legal compliance.</p>
                        </section>

                        <section class="mb-5">
                            <h4>7. Your Rights</h4>
                            <p>You have the following rights regarding your data:</p>
                            <ul>
                                <li><strong>Access:</strong> Request a copy of your personal data</li>
                                <li><strong>Correction:</strong> Request correction of inaccurate data</li>
                                <li><strong>Deletion:</strong> Request deletion of your data (subject to legal requirements)</li>
                                <li><strong>Portability:</strong> Request transfer of your data in a machine-readable format</li>
                                <li><strong>Opt-out:</strong> Unsubscribe from marketing communications</li>
                                <li><strong>Withdraw Consent:</strong> Withdraw previously given consent</li>
                            </ul>
                            <p>To exercise these rights, contact us at privacy@{{ str_replace(['http://', 'https://'], '', config('app.url')) }}</p>
                        </section>

                        <section class="mb-5">
                            <h4>8. Third-Party Links</h4>
                            <p>Our Service may contain links to third-party websites. We are not responsible for the privacy practices of these external sites. We encourage you to review their privacy policies.</p>
                        </section>

                        <section class="mb-5">
                            <h4>9. Children's Privacy</h4>
                            <p>Our Service is not intended for individuals under 18 years of age. We do not knowingly collect personal information from children. If you believe we have collected data from a minor, please contact us immediately.</p>
                        </section>

                        <section class="mb-5">
                            <h4>10. International Data Transfers</h4>
                            <p>Your information may be transferred to and processed in countries other than your own. We ensure appropriate safeguards are in place for such transfers.</p>
                        </section>

                        <section class="mb-5">
                            <h4>11. Changes to This Policy</h4>
                            <p>We may update this Privacy Policy from time to time. We will notify you of material changes by:</p>
                            <ul>
                                <li>Posting the updated policy on our website</li>
                                <li>Updating the "Last updated" date</li>
                                <li>Sending an email notification for significant changes</li>
                            </ul>
                        </section>

                        <section class="mb-5">
                            <h4>12. Contact Us</h4>
                            <p>If you have any questions about this Privacy Policy or our data practices, please contact us:</p>
                            <ul>
                                <li><strong>Email:</strong> privacy@{{ str_replace(['http://', 'https://'], '', config('app.url')) }}</li>
                                <li><strong>Address:</strong> [Your Business Address]</li>
                                <li><strong>Phone:</strong> [Your Contact Number]</li>
                            </ul>
                        </section>

                        <section class="mb-5">
                            <h4>13. Grievance Officer</h4>
                            <p>In accordance with Information Technology Act 2000 and rules made thereunder, the Grievance Officer for the purpose of this Policy is:</p>
                            <ul>
                                <li><strong>Name:</strong> [Grievance Officer Name]</li>
                                <li><strong>Email:</strong> grievance@{{ str_replace(['http://', 'https://'], '', config('app.url')) }}</li>
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