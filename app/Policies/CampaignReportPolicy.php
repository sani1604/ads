<?php
// app/Policies/CampaignReportPolicy.php

namespace App\Policies;

use App\Models\CampaignReport;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CampaignReportPolicy
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
    public function view(User $user, CampaignReport $report): bool
    {
        // Staff can view any report
        if ($user->isStaff()) {
            return true;
        }

        // Clients can only view their own reports
        return $user->id === $report->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isStaff();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CampaignReport $report): bool
    {
        return $user->isStaff();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CampaignReport $report): bool
    {
        return $user->isStaff();
    }

    /**
     * Determine whether the user can import reports.
     */
    public function import(User $user): bool
    {
        return $user->isStaff();
    }

    /**
     * Determine whether the user can export reports.
     */
    public function export(User $user): bool
    {
        return true; // Both staff and clients can export
    }
}