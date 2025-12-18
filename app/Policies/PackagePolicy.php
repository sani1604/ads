<?php
// app/Policies/PackagePolicy.php

namespace App\Policies;

use App\Models\Package;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PackagePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // Anyone can view packages
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Package $package): bool
    {
        // Staff can view any package
        if ($user->isStaff()) {
            return true;
        }

        // Clients can only view active packages
        return $package->is_active;
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
    public function update(User $user, Package $package): bool
    {
        return $user->isStaff();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Package $package): bool
    {
        // Only admin can delete
        if (!$user->isAdmin()) {
            return false;
        }

        // Cannot delete package with active subscriptions
        return !$package->subscriptions()->active()->exists();
    }

    /**
     * Determine whether the user can duplicate the package.
     */
    public function duplicate(User $user, Package $package): bool
    {
        return $user->isStaff();
    }

    /**
     * Determine whether the user can toggle status.
     */
    public function toggleStatus(User $user, Package $package): bool
    {
        return $user->isStaff();
    }
}