<?php
// app/Policies/NotificationPolicy.php

namespace App\Policies;

use App\Models\CustomNotification;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class NotificationPolicy
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
    public function view(User $user, CustomNotification $notification): bool
    {
        return $user->id === $notification->user_id;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CustomNotification $notification): bool
    {
        return $user->id === $notification->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CustomNotification $notification): bool
    {
        return $user->id === $notification->user_id;
    }

    /**
     * Determine whether the user can mark as read.
     */
    public function markAsRead(User $user, CustomNotification $notification): bool
    {
        return $user->id === $notification->user_id;
    }
}