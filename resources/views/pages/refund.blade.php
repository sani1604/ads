@extends('layouts.public')

@section('title', 'Refund Policy')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4 p-md-5">
                    <h1 class="h2 mb-4">Refund & Cancellation Policy</h1>
                    <p class="text-muted mb-4">Last updated: {{ now()->format('F d, Y') }}</p>

                    <div class="refund-content">
                        <div class="alert alert-info mb-4">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Quick Summary:</strong> Subscription fees are non-refundable. Wallet balances above ₹1,000 can be refunded with a 5% processing fee.
                        </div>

                        <section class="mb-5">
                            <h4>1. Subscription Refunds</h4>
                            
                            <h5>1.1 General Policy</h5>
                            <p>Subscription fees are <strong>non-refundable</strong> once the billing period has started. This includes:</p>
                            <ul>
                                <li>Monthly subscription fees</li>
                                <li>Quarterly subscription fees</li>
                                <li>Annual subscription fees</li>
                            </ul>

                            <h5>1.2 Exceptions</h5>
                            <p>We may consider refunds in the following exceptional circumstances:</p>
                            <ul>
                                <li><strong>Duplicate Payment:</strong> If you were charged twice for the same subscription period</li>
                                <li><strong>Technical Issues:</strong> If our service was unavailable for more than 72 consecutive hours during your billing period</li>
                                <li><strong>Billing Errors:</strong> If you were charged an incorrect amount</li>
                            </ul>
                        </section>

                        <section class="mb-5">
                            <h4>2. Wallet Balance Refunds</h4>
                            
                            <h5>2.1 Eligibility</h5>
                            <p>You may request a refund of your wallet balance if:</p>
                            <ul>
                                <li>Your account is in good standing</li>
                                <li>The balance was added within the last 90 days</li>
                                <li>The minimum refundable amount is ₹1,000</li>
                            </ul>

                            <h5>2.2 Processing Fee</h5>
                            <p>A <strong>5% processing fee</strong> will be deducted from the refund amount to cover payment gateway charges.</p>

                            <h5>2.3 Non-Refundable Wallet Credits</h5>
                            <p>The following wallet credits are non-refundable:</p>
                            <ul>
                                <li>Promotional credits or bonuses</li>
                                <li>Referral rewards</li>
                                <li>Compensation credits</li>
                                <li>Credits older than 90 days</li>
                            </ul>
                        </section>

                        <section class="mb-5">
                            <h4>3. Cancellation Policy</h4>
                            
                            <h5>3.1 How to Cancel</h5>
                            <p>You can cancel your subscription at any time through:</p>
                            <ul>
                                <li>Your account settings → Subscription → Cancel</li>
                                <li>Contacting our support team</li>
                            </ul>

                            <h5>3.2 Effect of Cancellation</h5>
                            <ul>
                                <li>Your subscription remains active until the end of the current billing period</li>
                                <li>No further charges will be made after cancellation</li>
                                <li>You will lose access to premium features after the billing period ends</li>
                                <li>Your data will be retained for 30 days after cancellation</li>
                            </ul>

                            <h5>3.3 Reactivation</h5>
                            <p>You can reactivate your subscription at any time. Your previous data will be available if reactivated within 30 days of cancellation.</p>
                        </section>

                        <section class="mb-5">
                            <h4>4. Lead Credits & Pay-Per-Lead</h4>
                            
                            <h5>4.1 Lead Credit Refunds</h5>
                            <p>Lead credits or costs deducted for leads are <strong>generally non-refundable</strong>.</p>

                            <h5>4.2 Exceptions for Lead Refunds</h5>
                            <p>We may credit your wallet for leads that are:</p>
                            <ul>
                                <li><strong>Duplicates:</strong> Same lead received multiple times</li>
                                <li><strong>Test Leads:</strong> Clearly identifiable test submissions</li>
                                <li><strong>Spam/Fake:</strong> Obviously fake or spam leads (subject to review)</li>
                            </ul>
                            <p>Report disputed leads within 7 days of receipt for review.</p>
                        </section>

                        <section class="mb-5">
                            <h4>5. Refund Process</h4>
                            
                            <h5>5.1 How to Request a Refund</h5>
                            <ol>
                                <li>Email us at billing@{{ str_replace(['http://', 'https://'], '', config('app.url')) }}</li>
                                <li>Include your account email, transaction ID, and reason for refund</li>
                                <li>Our team will review your request within 3 business days</li>
                            </ol>

                            <h5>5.2 Refund Timeline</h5>
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Payment Method</th>
                                        <th>Refund Timeline</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Credit/Debit Card</td>
                                        <td>5-7 business days</td>
                                    </tr>
                                    <tr>
                                        <td>UPI</td>
                                        <td>2-3 business days</td>
                                    </tr>
                                    <tr>
                                        <td>Net Banking</td>
                                        <td>5-10 business days</td>
                                    </tr>
                                    <tr>
                                        <td>Wallet (Paytm, etc.)</td>
                                        <td>1-2 business days</td>
                                    </tr>
                                </tbody>
                            </table>
                        </section>

                        <section class="mb-5">
                            <h4>6. Chargebacks</h4>
                            <p>If you initiate a chargeback with your bank without first contacting us:</p>
                            <ul>
                                <li>Your account will be immediately suspended</li>
                                <li>We will provide transaction evidence to the bank</li>
                                <li>A ₹500 dispute fee may be charged if the chargeback is found invalid</li>
                            </ul>
                            <p>Please contact us first to resolve any billing issues.</p>
                        </section>

                        <section class="mb-5">
                            <h4>7. Contact Us</h4>
                            <p>For refund requests or billing inquiries:</p>
                            <ul>
                                <li><strong>Email:</strong> billing@{{ str_replace(['http://', 'https://'], '', config('app.url')) }}</li>
                                <li><strong>Phone:</strong> [Your Contact Number]</li>
                                <li><strong>Hours:</strong> Monday - Friday, 10 AM - 6 PM IST</li>
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