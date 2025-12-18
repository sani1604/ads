<?php
// app/Policies/LeadPolicy.php

namespace App\Policies;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LeadPolicy
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
    public function view(User $user, Lead $lead): bool
    {
        // Staff can view any lead
        if ($user->isStaff()) {
            return true;
        }

        // Clients can only view their own leads
        return $user->id === $lead->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Staff can create leads for any client
        if ($user->isStaff()) {
            return true;
        }

        // Clients can create their own leads
        return $user->isClient();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Lead $lead): bool
    {
        // Staff can update any lead
        if ($user->isStaff()) {
            return true;
        }

        // Clients can update their own leads
        return $user->id === $lead->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Lead $lead): bool
    {
        // Only staff can delete leads
        return $user->isStaff();
    }

    /**
     * Determine whether the user can update the lead status.
     */
    public function updateStatus(User $user, Lead $lead): bool
    {
        // Staff can update status of any lead
        if ($user->isStaff()) {
            return true;
        }

        // Clients can update status of their own leads
        return $user->id === $lead->user_id;
    }

    /**
     * Determine whether the user can update the lead quality.
     */
    public function updateQuality(User $user, Lead $lead): bool
    {
        // Staff can update quality of any lead
        if ($user->isStaff()) {
            return true;
        }

        // Clients can update quality of their own leads
        return $user->id === $lead->user_id;
    }

    /**
     * Determine whether the user can add notes to the lead.
     */
    public function addNote(User $user, Lead $lead): bool
    {
        // Staff can add notes to any lead
        if ($user->isStaff()) {
            return true;
        }

        // Clients can add notes to their own leads
        return $user->id === $lead->user_id;
    }

    /**
     * Determine whether the user can export leads.
     */
    public function export(User $user): bool
    {
        return true; // Both staff and clients can export their own leads
    }

    /**
     * Determine whether the user can import leads.
     */
    public function import(User $user): bool
    {
        return $user->isStaff();
    }

    /**
     * Determine whether the user can assign leads.
     */
    public function assign(User $user, Lead $lead): bool
    {
        return $user->isStaff();
    }
}