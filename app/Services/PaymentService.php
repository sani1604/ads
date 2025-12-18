<?php
// app/Services/PaymentService.php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Package;
use App\Models\Setting;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use Razorpay\Api\Api;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    protected Api $razorpay;

    public function __construct()
    {
        $this->razorpay = new Api(
            config('services.razorpay.key'),
            config('services.razorpay.secret')
        );
    }

    /**
     * Create Razorpay order for subscription
     */
    public function createSubscriptionOrder(User $user, Package $package, float $discountAmount = 0): array
    {
        $taxRate = Setting::get('tax_rate', 18) / 100;
        $subtotal = $package->price - $discountAmount;
        $taxAmount = $subtotal * $taxRate;
        $totalAmount = $subtotal + $taxAmount;

        // Create Razorpay Order
        $razorpayOrder = $this->razorpay->order->create([
            'amount' => $totalAmount * 100, // Amount in paise
            'currency' => 'INR',
            'receipt' => 'SUB_' . time(),
            'notes' => [
                'user_id' => $user->id,
                'package_id' => $package->id,
                'type' => 'subscription',
            ],
        ]);

        // Create pending transaction
        $transaction = Transaction::create([
            'user_id' => $user->id,
            'transaction_id' => Transaction::generateTransactionId(),
            'type' => 'subscription',
            'payment_method' => 'razorpay',
            'amount' => $subtotal,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
            'currency' => 'INR',
            'status' => 'pending',
            'razorpay_order_id' => $razorpayOrder->id,
            'description' => "Subscription: {$package->name}",
            'meta_data' => [
                'package_id' => $package->id,
                'discount_amount' => $discountAmount,
            ],
        ]);

        return [
            'order_id' => $razorpayOrder->id,
            'transaction_id' => $transaction->id,
            'amount' => $totalAmount,
            'currency' => 'INR',
            'key' => config('services.razorpay.key'),
            'name' => Setting::get('site_name', 'Agency Portal'),
            'description' => $package->name . ' Subscription',
            'prefill' => [
                'name' => $user->name,
                'email' => $user->email,
                'contact' => $user->phone,
            ],
        ];
    }

    /**
     * Verify Razorpay payment and activate subscription
     */
    public function verifyAndActivateSubscription(
        string $razorpayOrderId,
        string $razorpayPaymentId,
        string $razorpaySignature,
        int $packageId
    ): Subscription {
        // Verify signature
        $this->verifySignature($razorpayOrderId, $razorpayPaymentId, $razorpaySignature);

        return DB::transaction(function () use ($razorpayOrderId, $razorpayPaymentId, $razorpaySignature, $packageId) {
            // Find transaction
            $transaction = Transaction::where('razorpay_order_id', $razorpayOrderId)->firstOrFail();
            
            // Mark transaction as completed
            $transaction->markAsCompleted($razorpayPaymentId, $razorpaySignature);

            // Get package
            $package = Package::findOrFail($packageId);
            $user = $transaction->user;

            // Create subscription
            $subscription = Subscription::create([
                'user_id' => $user->id,
                'package_id' => $package->id,
                'subscription_code' => Subscription::generateCode(),
                'status' => 'active',
                'amount' => $transaction->amount,
                'discount_amount' => $transaction->meta_data['discount_amount'] ?? 0,
                'tax_amount' => $transaction->tax_amount,
                'total_amount' => $transaction->total_amount,
                'start_date' => now(),
                'end_date' => now()->addDays($package->billing_cycle_days),
                'next_billing_date' => now()->addDays($package->billing_cycle_days),
                'billing_cycle_count' => 1,
            ]);

            // Update transaction with subscription ID
            $transaction->update(['subscription_id' => $subscription->id]);

            // Create invoice
            $this->createInvoice($subscription, $transaction);

            // Log activity
            ActivityLogService::log(
                'subscription_created',
                "Subscribed to {$package->name}",
                $subscription,
                ['package' => $package->name, 'amount' => $transaction->total_amount]
            );

            // Send notification
            NotificationService::subscriptionActivated($user, $subscription);

            return $subscription;
        });
    }

    /**
     * Create wallet recharge order
     */
    public function createWalletRechargeOrder(User $user, float $amount): array
    {
        $taxRate = Setting::get('tax_rate', 18) / 100;
        $taxAmount = $amount * $taxRate;
        $totalAmount = $amount + $taxAmount;

        $razorpayOrder = $this->razorpay->order->create([
            'amount' => $totalAmount * 100,
            'currency' => 'INR',
            'receipt' => 'WALLET_' . time(),
            'notes' => [
                'user_id' => $user->id,
                'type' => 'wallet_recharge',
            ],
        ]);

        $transaction = Transaction::create([
            'user_id' => $user->id,
            'transaction_id' => Transaction::generateTransactionId(),
            'type' => 'wallet_recharge',
            'payment_method' => 'razorpay',
            'amount' => $amount,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
            'currency' => 'INR',
            'status' => 'pending',
            'razorpay_order_id' => $razorpayOrder->id,
            'description' => 'Wallet Recharge',
        ]);

        return [
            'order_id' => $razorpayOrder->id,
            'transaction_id' => $transaction->id,
            'amount' => $totalAmount,
            'currency' => 'INR',
            'key' => config('services.razorpay.key'),
            'name' => Setting::get('site_name', 'Agency Portal'),
            'description' => 'Wallet Recharge - ₹' . number_format($amount, 2),
            'prefill' => [
                'name' => $user->name,
                'email' => $user->email,
                'contact' => $user->phone,
            ],
        ];
    }

    /**
     * Verify and credit wallet
     */
    public function verifyAndCreditWallet(
        string $razorpayOrderId,
        string $razorpayPaymentId,
        string $razorpaySignature
    ): Transaction {
        $this->verifySignature($razorpayOrderId, $razorpayPaymentId, $razorpaySignature);

        return DB::transaction(function () use ($razorpayOrderId, $razorpayPaymentId, $razorpaySignature) {
            $transaction = Transaction::where('razorpay_order_id', $razorpayOrderId)->firstOrFail();
            
            $transaction->markAsCompleted($razorpayPaymentId, $razorpaySignature);

            $user = $transaction->user;

            // Credit wallet
            $user->creditWallet(
                $transaction->amount,
                'Wallet Recharge via Razorpay',
                'transaction',
                $transaction->id
            );

            // Create invoice
            $this->createWalletInvoice($user, $transaction);

            // Log activity
            ActivityLogService::log(
                'wallet_recharged',
                "Wallet recharged with ₹{$transaction->amount}",
                $transaction,
                ['amount' => $transaction->amount]
            );

            // Send notification
            NotificationService::walletRecharged($user, $transaction->amount);

            return $transaction;
        });
    }

    /**
     * Verify Razorpay signature
     */
    protected function verifySignature(string $orderId, string $paymentId, string $signature): void
    {
        $expectedSignature = hash_hmac(
            'sha256',
            $orderId . '|' . $paymentId,
            config('services.razorpay.secret')
        );

        if ($expectedSignature !== $signature) {
            throw new \Exception('Invalid payment signature');
        }
    }

    /**
     * Create invoice for subscription
     */
    protected function createInvoice(Subscription $subscription, Transaction $transaction): Invoice
    {
        $user = $subscription->user;
        $package = $subscription->package;

        return Invoice::create([
            'user_id' => $user->id,
            'subscription_id' => $subscription->id,
            'transaction_id' => $transaction->id,
            'invoice_number' => Invoice::generateInvoiceNumber(),
            'type' => 'subscription',
            'status' => 'paid',
            'invoice_date' => now(),
            'due_date' => now(),
            'subtotal' => $subscription->amount,
            'discount_amount' => $subscription->discount_amount,
            'tax_rate' => Setting::get('tax_rate', 18),
            'tax_amount' => $subscription->tax_amount,
            'total_amount' => $subscription->total_amount,
            'line_items' => [
                [
                    'description' => $package->name . ' - ' . $package->serviceCategory->name,
                    'quantity' => 1,
                    'rate' => $subscription->amount,
                    'amount' => $subscription->amount,
                ],
            ],
            'billing_address' => [
                'name' => $user->name,
                'company' => $user->company_name,
                'address' => $user->address,
                'city' => $user->city,
                'state' => $user->state,
                'postal_code' => $user->postal_code,
                'gst_number' => $user->gst_number,
            ],
            'paid_at' => now(),
        ]);
    }

    /**
     * Create invoice for wallet recharge
     */
    protected function createWalletInvoice(User $user, Transaction $transaction): Invoice
    {
        return Invoice::create([
            'user_id' => $user->id,
            'transaction_id' => $transaction->id,
            'invoice_number' => Invoice::generateInvoiceNumber(),
            'type' => 'wallet_recharge',
            'status' => 'paid',
            'invoice_date' => now(),
            'due_date' => now(),
            'subtotal' => $transaction->amount,
            'discount_amount' => 0,
            'tax_rate' => Setting::get('tax_rate', 18),
            'tax_amount' => $transaction->tax_amount,
            'total_amount' => $transaction->total_amount,
            'line_items' => [
                [
                    'description' => 'Ad Spend Wallet Recharge',
                    'quantity' => 1,
                    'rate' => $transaction->amount,
                    'amount' => $transaction->amount,
                ],
            ],
            'billing_address' => [
                'name' => $user->name,
                'company' => $user->company_name,
                'address' => $user->address,
                'city' => $user->city,
                'state' => $user->state,
                'postal_code' => $user->postal_code,
                'gst_number' => $user->gst_number,
            ],
            'paid_at' => now(),
        ]);
    }

    /**
     * Process subscription renewal
     */
    public function renewSubscription(Subscription $subscription): bool
    {
        $user = $subscription->user;
        $package = $subscription->package;

        // Check wallet balance
        if ($user->wallet_balance >= $subscription->total_amount) {
            return DB::transaction(function () use ($subscription, $user, $package) {
                // Debit from wallet
                $user->debitWallet(
                    $subscription->total_amount,
                    "Subscription renewal: {$package->name}",
                    'subscription',
                    $subscription->id
                );

                // Renew subscription
                $subscription->renew();

                // Create transaction
                $transaction = Transaction::create([
                    'user_id' => $user->id,
                    'subscription_id' => $subscription->id,
                    'transaction_id' => Transaction::generateTransactionId(),
                    'type' => 'subscription',
                    'payment_method' => 'wallet',
                    'amount' => $subscription->amount,
                    'tax_amount' => $subscription->tax_amount,
                    'total_amount' => $subscription->total_amount,
                    'currency' => 'INR',
                    'status' => 'completed',
                    'description' => "Subscription Renewal: {$package->name}",
                    'paid_at' => now(),
                ]);

                // Create invoice
                $this->createInvoice($subscription, $transaction);

                // Send notification
                NotificationService::subscriptionRenewed($user, $subscription);

                return true;
            });
        }

        return false;
    }
}