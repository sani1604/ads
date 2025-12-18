<?php
// app/Policies/SubscriptionPolicy.php

namespace App\Policies;

use App\Models\Subscription;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SubscriptionPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // Both staff and clients can view subscriptions
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Subscription $subscription): bool
    {
        // Staff can view any subscription
        if ($user->isStaff()) {
            return true;
        }

        // Clients can only view their own subscriptions
        return $user->id === $subscription->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Staff can create subscriptions for clients
        if ($user->isStaff()) {
            return true;
        }

        // Clients can create their own subscriptions
        return $user->isClient();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Subscription $subscription): bool
    {
        // Only staff can update subscriptions
        return $user->isStaff();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Subscription $subscription): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can cancel the subscription.
     */
    public function cancel(User $user, Subscription $subscription): bool
    {
        // Staff can cancel any subscription
        if ($user->isStaff()) {
            return $subscription->canCancel();
        }

        // Clients can cancel their own subscriptions
        if ($user->id === $subscription->user_id) {
            return $subscription->canCancel();
        }

        return false;
    }

    /**
     * Determine whether the user can renew the subscription.
     */
    public function renew(User $user, Subscription $subscription): bool
    {
        // Staff can renew any subscription
        if ($user->isStaff()) {
            return $subscription->canRenew();
        }

        // Clients can renew their own subscriptions
        if ($user->id === $subscription->user_id) {
            return $subscription->canRenew();
        }

        return false;
    }

    /**
     * Determine whether the user can pause the subscription.
     */
    public function pause(User $user, Subscription $subscription): bool
    {
        return $user->isStaff() && $subscription->status === 'active';
    }

    /**
     * Determine whether the user can resume the subscription.
     */
    public function resume(User $user, Subscription $subscription): bool
    {
        return $user->isStaff() && $subscription->status === 'paused';
    }

    /**
     * Determine whether the user can extend the subscription.
     */
    public function extend(User $user, Subscription $subscription): bool
    {
        return $user->isStaff();
    }

    /**
     * Determine whether the user can change the package.
     */
    public function changePackage(User $user, Subscription $subscription): bool
    {
        return $user->isStaff() && $subscription->isActive();
    }
}