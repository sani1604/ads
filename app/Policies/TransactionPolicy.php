<?php
// app/Policies/TransactionPolicy.php

namespace App\Policies;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TransactionPolicy
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
    public function view(User $user, Transaction $transaction): bool
    {
        // Staff can view any transaction
        if ($user->isStaff()) {
            return true;
        }

        // Clients can only view their own transactions
        return $user->id === $transaction->user_id;
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
    public function update(User $user, Transaction $transaction): bool
    {
        // Can only update pending transactions
        if ($transaction->status !== 'pending') {
            return $user->isAdmin();
        }

        return $user->isStaff();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Transaction $transaction): bool
    {
        return false; // Transactions should never be deleted
    }

    /**
     * Determine whether the user can refund the transaction.
     */
    public function refund(User $user, Transaction $transaction): bool
    {
        return $user->isAdmin() && $transaction->status === 'completed';
    }

    /**
     * Determine whether the user can update status.
     */
    public function updateStatus(User $user, Transaction $transaction): bool
    {
        return $user->isStaff();
    }
}