<?php
// app/Policies/UserPolicy.php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isStaff();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        // Staff can view any user
        if ($user->isStaff()) {
            return true;
        }

        // Users can view their own profile
        return $user->id === $model->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        // Admin can update any user
        if ($user->isAdmin()) {
            return true;
        }

        // Manager can update clients only
        if ($user->isManager() && $model->isClient()) {
            return true;
        }

        // Users can update their own profile
        return $user->id === $model->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        // Only admin can delete
        if (!$user->isAdmin()) {
            return false;
        }

        // Cannot delete self
        if ($user->id === $model->id) {
            return false;
        }

        // Cannot delete the last admin
        if ($model->isAdmin() && User::where('role', 'admin')->count() <= 1) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        return $user->isAdmin() && $user->id !== $model->id;
    }

    /**
     * Determine whether the user can impersonate the model.
     */
    public function impersonate(User $user, User $model): bool
    {
        // Only admins can impersonate
        if (!$user->isAdmin()) {
            return false;
        }

        // Cannot impersonate self
        if ($user->id === $model->id) {
            return false;
        }

        // Can only impersonate clients
        return $model->isClient();
    }

    /**
     * Determine whether the user can toggle status.
     */
    public function toggleStatus(User $user, User $model): bool
    {
        if (!$user->isStaff()) {
            return false;
        }

        // Cannot toggle own status
        if ($user->id === $model->id) {
            return false;
        }

        // Managers can only toggle client status
        if ($user->isManager()) {
            return $model->isClient();
        }

        return $user->isAdmin();
    }

    /**
     * Determine whether the user can manage wallet.
     */
    public function manageWallet(User $user, User $model): bool
    {
        return $user->isStaff() && $model->isClient();
    }
}