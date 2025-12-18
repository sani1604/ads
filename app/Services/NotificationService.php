<?php
// app/Services/NotificationService.php

namespace App\Services;

use App\Models\CustomNotification;
use App\Models\Lead;
use App\Models\Subscription;
use App\Models\User;
use App\Models\Creative;
use App\Models\SupportTicket;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    /**
     * New lead received
     */

       public static function send(
        User $user,
        string $type,
        string $title,
        string $message,
        ?string $actionUrl = null,
        array $data = []
    ): CustomNotification {
        return CustomNotification::create([
            'user_id'    => $user->id,
            'type'       => $type,
            'title'      => $title,
            'message'    => $message,
            'icon'       => null,         // icon can be auto-resolved in model accessor
            'action_url' => $actionUrl,
            'data'       => $data,
            'is_read'    => false,
            'read_at'    => null,
        ]);
    }

    
    public static function newLeadReceived(User $user, Lead $lead): CustomNotification
    {
        return CustomNotification::send(
            $user,
            'lead_received',
            'New Lead Received! 🎉',
            "You have a new lead: {$lead->name} from {$lead->source}",
            route('client.leads.show', $lead),
            ['lead_id' => $lead->id]
        );
    }

    /**
     * Creative approved
     */
    public static function creativeApproved(User $user, Creative $creative): CustomNotification
    {
        return CustomNotification::send(
            $user,
            'creative_approved',
            'Creative Approved ✅',
            "Your creative '{$creative->title}' has been approved and is ready for publishing.",
            route('client.creatives.show', $creative),
            ['creative_id' => $creative->id]
        );
    }

    /**
     * Creative needs changes
     */
    public static function creativeNeedsChanges(User $user, Creative $creative): CustomNotification
    {
        return CustomNotification::send(
            $user,
            'changes_requested',
            'Creative Changes Requested',
            "Please review the feedback on '{$creative->title}' and make necessary changes.",
            route('client.creatives.show', $creative),
            ['creative_id' => $creative->id]
        );
    }

    /**
     * New creative for approval (Admin)
     */
    public static function newCreativeForApproval(User $admin, Creative $creative): CustomNotification
    {
        return CustomNotification::send(
            $admin,
            'creative_pending',
            'New Creative for Approval',
            "'{$creative->title}' from {$creative->user->company_name} needs approval.",
            route('admin.creatives.show', $creative),
            ['creative_id' => $creative->id]
        );
    }

    /**
     * Subscription activated
     */
    public static function subscriptionActivated(User $user, Subscription $subscription): CustomNotification
    {
        return CustomNotification::send(
            $user,
            'payment_success',
            'Subscription Activated! 🚀',
            "Your {$subscription->package->name} subscription is now active. Let's grow your business!",
            route('client.subscription.index'),
            ['subscription_id' => $subscription->id]
        );
    }

    /**
     * Subscription renewed
     */
    public static function subscriptionRenewed(User $user, Subscription $subscription): CustomNotification
    {
        return CustomNotification::send(
            $user,
            'payment_success',
            'Subscription Renewed',
            "Your {$subscription->package->name} subscription has been renewed successfully.",
            route('client.subscription.index'),
            ['subscription_id' => $subscription->id]
        );
    }

    /**
     * Subscription expiring soon
     */
    public static function subscriptionExpiringSoon(User $user, Subscription $subscription): CustomNotification
    {
        $daysLeft = $subscription->days_remaining;

        return CustomNotification::send(
            $user,
            'subscription_expiring',
            'Subscription Expiring Soon ⚠️',
            "Your subscription expires in {$daysLeft} days. Recharge your wallet to ensure uninterrupted service.",
            route('client.wallet.index'),
            ['subscription_id' => $subscription->id, 'days_left' => $daysLeft]
        );
    }

    /**
     * Wallet recharged
     */
    public static function walletRecharged(User $user, float $amount): CustomNotification
    {
        return CustomNotification::send(
            $user,
            'payment_success',
            'Wallet Recharged! 💰',
            "₹" . number_format($amount, 2) . " has been added to your wallet.",
            route('client.wallet.index'),
            ['amount' => $amount]
        );
    }

    /**
     * Low wallet balance warning
     */
    public static function lowWalletBalance(User $user): CustomNotification
    {
        return CustomNotification::send(
            $user,
            'wallet_low',
            'Low Wallet Balance ⚠️',
            "Your wallet balance is running low. Recharge now to continue your ad campaigns.",
            route('client.wallet.recharge'),
            ['balance' => $user->wallet_balance]
        );
    }

    /**
     * Support ticket reply
     */
    public static function ticketReply(User $user, SupportTicket $ticket): CustomNotification
    {
        return CustomNotification::send(
            $user,
            'ticket_reply',
            'New Reply on Your Ticket',
            "There's a new response on ticket #{$ticket->ticket_number}",
            route('client.support.show', $ticket),
            ['ticket_id' => $ticket->id]
        );
    }

    /**
     * Mark all as read for user
     */
    public static function markAllAsRead(User $user): int
    {
        return $user->notifications()
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }
}