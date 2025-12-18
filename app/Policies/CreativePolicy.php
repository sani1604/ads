<?php
// app/Policies/CreativePolicy.php

namespace App\Policies;

use App\Models\Creative;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CreativePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Creative $creative): bool
    {
        // Staff can view any creative
        if ($user->isStaff()) {
            return true;
        }

        // Clients can only view their own creatives
        return $user->id === $creative->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Staff can always create
        if ($user->isStaff()) {
            return true;
        }

        // Clients need active subscription and remaining quota
        if (!$user->hasActiveSubscription()) {
            return false;
        }

        $subscription = $user->activeSubscription;
        return $subscription->getCreativesRemainingThisMonth() > 0;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Creative $creative): bool
    {
        // Staff can update any creative
        if ($user->isStaff()) {
            return true;
        }

        // Clients can only update their own creatives that are not approved
        if ($user->id === $creative->user_id) {
            return !in_array($creative->status, ['approved', 'published']);
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Creative $creative): bool
    {
        // Staff can delete any creative
        if ($user->isStaff()) {
            return true;
        }

        // Clients can only delete their own draft creatives
        if ($user->id === $creative->user_id) {
            return $creative->status === 'draft';
        }

        return false;
    }

    /**
     * Determine whether the user can approve the creative.
     */
    public function approve(User $user, Creative $creative): bool
    {
        // Only staff can approve on admin side
        // Clients can approve creatives pending their approval
        if ($user->isStaff()) {
            return $creative->isPending();
        }

        // Client approval (when agency submits for client approval)
        if ($user->id === $creative->user_id) {
            return $creative->isPending();
        }

        return false;
    }

    /**
     * Determine whether the user can request changes.
     */
    public function requestChanges(User $user, Creative $creative): bool
    {
        // Staff can request changes on pending creatives
        if ($user->isStaff()) {
            return $creative->isPending();
        }

        // Clients can request changes on their pending creatives
        if ($user->id === $creative->user_id) {
            return $creative->isPending();
        }

        return false;
    }

    /**
     * Determine whether the user can reject the creative.
     */
    public function reject(User $user, Creative $creative): bool
    {
        return $user->isStaff() && $creative->isPending();
    }

    /**
     * Determine whether the user can submit for approval.
     */
    public function submitForApproval(User $user, Creative $creative): bool
    {
        // Staff can submit any draft creative
        if ($user->isStaff()) {
            return $creative->status === 'draft';
        }

        // Clients can submit their own draft creatives
        if ($user->id === $creative->user_id) {
            return $creative->status === 'draft';
        }

        return false;
    }

    /**
     * Determine whether the user can mark as published.
     */
    public function markPublished(User $user, Creative $creative): bool
    {
        return $user->isStaff() && $creative->status === 'approved';
    }

    /**
     * Determine whether the user can add comments.
     */
    public function addComment(User $user, Creative $creative): bool
    {
        // Staff can comment on any creative
        if ($user->isStaff()) {
            return true;
        }

        // Clients can comment on their own creatives
        return $user->id === $creative->user_id;
    }

    /**
     * Determine whether the user can download the creative.
     */
    public function download(User $user, Creative $creative): bool
    {
        // Staff can download any creative
        if ($user->isStaff()) {
            return true;
        }

        // Clients can download their own creatives
        return $user->id === $creative->user_id;
    }
}