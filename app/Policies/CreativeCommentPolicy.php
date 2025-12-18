<?php
// app/Policies/CreativeCommentPolicy.php

namespace App\Policies;

use App\Models\CreativeComment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CreativeCommentPolicy
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
    public function view(User $user, CreativeComment $comment): bool
    {
        // Staff can view any comment
        if ($user->isStaff()) {
            return true;
        }

        // Clients can view comments on their own creatives
        return $user->id === $comment->creative->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true; // Handled by creative policy
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CreativeComment $comment): bool
    {
        // Users can only update their own comments
        if ($user->id === $comment->user_id) {
            // Cannot update if older than 15 minutes
            return $comment->created_at->diffInMinutes(now()) <= 15;
        }

        return $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CreativeComment $comment): bool
    {
        // Staff can delete any comment
        if ($user->isStaff()) {
            return true;
        }

        // Users can delete their own comments within 15 minutes
        if ($user->id === $comment->user_id) {
            return $comment->created_at->diffInMinutes(now()) <= 15;
        }

        return false;
    }

    /**
     * Determine whether the user can resolve the comment.
     */
    public function resolve(User $user, CreativeComment $comment): bool
    {
        // Staff can resolve any comment
        if ($user->isStaff()) {
            return true;
        }

        // Creative owner can resolve comments
        return $user->id === $comment->creative->user_id;
    }
}