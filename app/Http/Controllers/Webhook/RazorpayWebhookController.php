<?php
// app/Http/Controllers/Webhook/RazorpayWebhookController.php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Services\NotificationService;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RazorpayWebhookController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Handle Razorpay webhook
     */
    public function handle(Request $request)
    {
        $payload = $request->all();

        Log::info('Razorpay Webhook received', ['event' => $payload['event'] ?? 'unknown']);

        // Verify webhook signature
        $webhookSecret = config('services.razorpay.webhook_secret');

        if ($webhookSecret) {
            $signature = $request->header('X-Razorpay-Signature');
            $expectedSignature = hash_hmac('sha256', $request->getContent(), $webhookSecret);

            if ($signature !== $expectedSignature) {
                Log::warning('Razorpay webhook signature verification failed');
                return response('Invalid signature', 400);
            }
        }

        try {
            $event = $payload['event'] ?? '';

            switch ($event) {
                case 'payment.captured':
                    $this->handlePaymentCaptured($payload['payload']['payment']['entity'] ?? []);
                    break;

                case 'payment.failed':
                    $this->handlePaymentFailed($payload['payload']['payment']['entity'] ?? []);
                    break;

                case 'subscription.charged':
                    $this->handleSubscriptionCharged($payload['payload']['subscription']['entity'] ?? [], $payload['payload']['payment']['entity'] ?? []);
                    break;

                case 'subscription.cancelled':
                    $this->handleSubscriptionCancelled($payload['payload']['subscription']['entity'] ?? []);
                    break;

                case 'subscription.halted':
                    $this->handleSubscriptionHalted($payload['payload']['subscription']['entity'] ?? []);
                    break;

                case 'refund.created':
                    $this->handleRefundCreated($payload['payload']['refund']['entity'] ?? []);
                    break;

                default:
                    Log::info('Unhandled Razorpay event', ['event' => $event]);
            }

            return response('OK', 200);
        } catch (\Exception $e) {
            Log::error('Razorpay webhook processing error', [
                'error' => $e->getMessage(),
                'event' => $payload['event'] ?? 'unknown',
            ]);

            return response('OK', 200);
        }
    }

    /**
     * Handle payment captured event
     */
    protected function handlePaymentCaptured(array $payment): void
    {
        $orderId = $payment['order_id'] ?? null;
        $paymentId = $payment['id'] ?? null;

        if (!$orderId) {
            return;
        }

        $transaction = Transaction::where('razorpay_order_id', $orderId)->first();

        if (!$transaction) {
            Log::warning('Transaction not found for captured payment', ['order_id' => $orderId]);
            return;
        }

        if ($transaction->status === 'completed') {
            return; // Already processed
        }

        $transaction->update([
            'status' => 'completed',
            'razorpay_payment_id' => $paymentId,
            'paid_at' => now(),
            'payment_response' => $payment,
        ]);

        // Handle based on type
        if ($transaction->type === 'wallet_recharge') {
            $transaction->user->creditWallet(
                $transaction->amount,
                'Wallet recharge via Razorpay',
                'transaction',
                $transaction->id
            );

            NotificationService::walletRecharged($transaction->user, $transaction->amount);
        }

        Log::info('Payment captured processed', ['transaction_id' => $transaction->id]);
    }

    /**
     * Handle payment failed event
     */
    protected function handlePaymentFailed(array $payment): void
    {
        $orderId = $payment['order_id'] ?? null;

        if (!$orderId) {
            return;
        }

        $transaction = Transaction::where('razorpay_order_id', $orderId)->first();

        if (!$transaction || $transaction->status !== 'pending') {
            return;
        }

        $transaction->update([
            'status' => 'failed',
            'payment_response' => $payment,
        ]);

        // Notify user
        NotificationService::send(
            $transaction->user,
            'payment_failed',
            'Payment Failed',
            'Your payment could not be processed. Please try again.',
            route('client.wallet.recharge'),
            ['transaction_id' => $transaction->id]
        );

        Log::info('Payment failed processed', ['transaction_id' => $transaction->id]);
    }

    /**
     * Handle subscription charged (recurring payment)
     */
    protected function handleSubscriptionCharged(array $subscriptionData, array $payment): void
    {
        $razorpaySubscriptionId = $subscriptionData['id'] ?? null;

        if (!$razorpaySubscriptionId) {
            return;
        }

        $subscription = Subscription::where('razorpay_subscription_id', $razorpaySubscriptionId)->first();

        if (!$subscription) {
            Log::warning('Subscription not found for charge', ['razorpay_id' => $razorpaySubscriptionId]);
            return;
        }

        // Renew subscription
        $this->paymentService->renewSubscription($subscription);

        Log::info('Subscription charged and renewed', ['subscription_id' => $subscription->id]);
    }

    /**
     * Handle subscription cancelled
     */
    protected function handleSubscriptionCancelled(array $subscriptionData): void
    {
        $razorpaySubscriptionId = $subscriptionData['id'] ?? null;

        if (!$razorpaySubscriptionId) {
            return;
        }

        $subscription = Subscription::where('razorpay_subscription_id', $razorpaySubscriptionId)->first();

        if (!$subscription) {
            return;
        }

        $subscription->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => 'Cancelled via Razorpay',
        ]);

        NotificationService::send(
            $subscription->user,
            'subscription_cancelled',
            'Subscription Cancelled',
            'Your subscription has been cancelled.',
            route('client.subscription.index'),
            ['subscription_id' => $subscription->id]
        );

        Log::info('Subscription cancelled', ['subscription_id' => $subscription->id]);
    }

    /**
     * Handle subscription halted (payment failed repeatedly)
     */
    protected function handleSubscriptionHalted(array $subscriptionData): void
    {
        $razorpaySubscriptionId = $subscriptionData['id'] ?? null;

        if (!$razorpaySubscriptionId) {
            return;
        }

        $subscription = Subscription::where('razorpay_subscription_id', $razorpaySubscriptionId)->first();

        if (!$subscription) {
            return;
        }

        $subscription->update([
            'status' => 'paused',
            'meta_data' => array_merge($subscription->meta_data ?? [], [
                'halted_at' => now()->toIso8601String(),
                'halted_reason' => 'Payment failed',
            ]),
        ]);

        NotificationService::send(
            $subscription->user,
            'subscription_halted',
            'Subscription Payment Failed',
            'Your subscription has been paused due to payment failure. Please update your payment method.',
            route('client.subscription.index'),
            ['subscription_id' => $subscription->id]
        );

        Log::info('Subscription halted', ['subscription_id' => $subscription->id]);
    }

    /**
     * Handle refund created
     */
    protected function handleRefundCreated(array $refund): void
    {
        $paymentId = $refund['payment_id'] ?? null;
        $amount = ($refund['amount'] ?? 0) / 100; // Convert from paise

        if (!$paymentId) {
            return;
        }

        $transaction = Transaction::where('razorpay_payment_id', $paymentId)->first();

        if (!$transaction) {
            return;
        }

        // Create refund transaction
        Transaction::create([
            'user_id' => $transaction->user_id,
            'subscription_id' => $transaction->subscription_id,
            'transaction_id' => Transaction::generateTransactionId(),
            'type' => 'refund',
            'payment_method' => 'razorpay',
            'amount' => -$amount,
            'tax_amount' => 0,
            'total_amount' => -$amount,
            'currency' => 'INR',
            'status' => 'completed',
            'razorpay_payment_id' => $refund['id'] ?? null,
            'description' => 'Refund processed via Razorpay',
            'meta_data' => [
                'original_transaction_id' => $transaction->id,
                'razorpay_refund' => $refund,
            ],
            'paid_at' => now(),
        ]);

        // Update original transaction
        if ($amount >= $transaction->total_amount) {
            $transaction->update(['status' => 'refunded']);
        }

        NotificationService::send(
            $transaction->user,
            'refund_processed',
            'Refund Processed',
            "A refund of ₹{$amount} has been processed to your account.",
            route('client.transactions.index'),
            ['amount' => $amount]
        );

        Log::info('Refund processed', ['transaction_id' => $transaction->id, 'amount' => $amount]);
    }
}