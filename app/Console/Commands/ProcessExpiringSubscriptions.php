<?php
// app/Console/Commands/ProcessExpiringSubscriptions.php

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Services\NotificationService;
use App\Services\PaymentService;
use Illuminate\Console\Command;

class ProcessExpiringSubscriptions extends Command
{
    protected $signature = 'subscriptions:process-expiring';
    protected $description = 'Process expiring subscriptions - send reminders and auto-renew';

    public function handle(PaymentService $paymentService)
    {
        $this->info('Processing expiring subscriptions...');

        // Send reminders for subscriptions expiring in 7 days
        $this->sendExpiryReminders(7);

        // Send reminders for subscriptions expiring in 3 days
        $this->sendExpiryReminders(3);

        // Send reminders for subscriptions expiring tomorrow
        $this->sendExpiryReminders(1);

        // Process auto-renewals for due subscriptions
        $this->processAutoRenewals($paymentService);

        // Mark expired subscriptions
        $this->markExpiredSubscriptions();

        $this->info('Finished processing subscriptions.');
    }

    protected function sendExpiryReminders(int $days): void
    {
        $subscriptions = Subscription::with(['user', 'package'])
            ->active()
            ->whereDate('end_date', now()->addDays($days)->toDateString())
            ->get();

        foreach ($subscriptions as $subscription) {
            NotificationService::subscriptionExpiringSoon($subscription->user, $subscription);
            $this->line("Sent {$days}-day reminder to: {$subscription->user->email}");
        }
    }

    protected function processAutoRenewals(PaymentService $paymentService): void
    {
        $subscriptions = Subscription::with(['user', 'package'])
            ->active()
            ->whereDate('next_billing_date', '<=', now()->toDateString())
            ->get();

        foreach ($subscriptions as $subscription) {
            $user = $subscription->user;

            // Check if user has sufficient wallet balance
            if ($user->wallet_balance >= $subscription->total_amount) {
                try {
                    $paymentService->renewSubscription($subscription);
                    $this->line("Auto-renewed subscription for: {$user->email}");
                } catch (\Exception $e) {
                    $this->error("Failed to renew for {$user->email}: {$e->getMessage()}");
                }
            } else {
                // Send low balance notification
                NotificationService::lowWalletBalance($user);
                $this->line("Sent low balance notification to: {$user->email}");
            }
        }
    }

    protected function markExpiredSubscriptions(): void
    {
        $count = Subscription::where('status', 'active')
            ->whereDate('end_date', '<', now()->toDateString())
            ->update(['status' => 'expired']);

        if ($count > 0) {
            $this->line("Marked {$count} subscriptions as expired.");
        }
    }
}