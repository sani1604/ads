<?php
// app/Policies/SupportTicketPolicy.php

namespace App\Policies;

use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SupportTicketPolicy
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
    public function view(User $user, SupportTicket $ticket): bool
    {
        // Staff can view any ticket
        if ($user->isStaff()) {
            return true;
        }

        // Clients can only view their own tickets
        return $user->id === $ticket->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true; // Anyone can create support tickets
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, SupportTicket $ticket): bool
    {
        // Staff can update any ticket
        if ($user->isStaff()) {
            return true;
        }

        // Clients can only update their own open tickets
        if ($user->id === $ticket->user_id) {
            return !in_array($ticket->status, ['resolved', 'closed']);
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, SupportTicket $ticket): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can reply to the ticket.
     */
    public function reply(User $user, SupportTicket $ticket): bool
    {
        // Staff can reply to any ticket
        if ($user->isStaff()) {
            return true;
        }

        // Clients can reply to their own non-closed tickets
        if ($user->id === $ticket->user_id) {
            return $ticket->status !== 'closed';
        }

        return false;
    }

    /**
     * Determine whether the user can close the ticket.
     */
    public function close(User $user, SupportTicket $ticket): bool
    {
        // Staff can close any ticket
        if ($user->isStaff()) {
            return true;
        }

        // Clients can close their own tickets
        return $user->id === $ticket->user_id;
    }

    /**
     * Determine whether the user can reopen the ticket.
     */
    public function reopen(User $user, SupportTicket $ticket): bool
    {
        // Staff can reopen any ticket
        if ($user->isStaff()) {
            return in_array($ticket->status, ['resolved', 'closed']);
        }

        // Clients can reopen their own resolved/closed tickets
        if ($user->id === $ticket->user_id) {
            return in_array($ticket->status, ['resolved', 'closed']);
        }

        return false;
    }

    /**
     * Determine whether the user can resolve the ticket.
     */
    public function resolve(User $user, SupportTicket $ticket): bool
    {
        return $user->isStaff() && !in_array($ticket->status, ['resolved', 'closed']);
    }

    /**
     * Determine whether the user can assign the ticket.
     */
    public function assign(User $user, SupportTicket $ticket): bool
    {
        return $user->isStaff();
    }

    /**
     * Determine whether the user can update priority.
     */
    public function updatePriority(User $user, SupportTicket $ticket): bool
    {
        return $user->isStaff();
    }

    /**
     * Determine whether the user can merge tickets.
     */
    public function merge(User $user): bool
    {
        return $user->isStaff();
    }

    /**
     * Determine whether the user can add internal notes.
     */
    public function addInternalNote(User $user, SupportTicket $ticket): bool
    {
        return $user->isStaff();
    }
}