<?php
// app/Policies/InvoicePolicy.php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class InvoicePolicy
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
    public function view(User $user, Invoice $invoice): bool
    {
        // Staff can view any invoice
        if ($user->isStaff()) {
            return true;
        }

        // Clients can only view their own invoices
        return $user->id === $invoice->user_id;
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
    public function update(User $user, Invoice $invoice): bool
    {
        // Cannot update paid invoices
        if ($invoice->status === 'paid') {
            return false;
        }

        return $user->isStaff();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Invoice $invoice): bool
    {
        // Cannot delete paid invoices
        if ($invoice->status === 'paid') {
            return false;
        }

        return $user->isAdmin();
    }

    /**
     * Determine whether the user can download the invoice.
     */
    public function download(User $user, Invoice $invoice): bool
    {
        // Staff can download any invoice
        if ($user->isStaff()) {
            return true;
        }

        // Clients can download their own invoices
        return $user->id === $invoice->user_id;
    }

    /**
     * Determine whether the user can mark as paid.
     */
    public function markAsPaid(User $user, Invoice $invoice): bool
    {
        return $user->isStaff() && $invoice->status !== 'paid';
    }

    /**
     * Determine whether the user can send the invoice.
     */
    public function send(User $user, Invoice $invoice): bool
    {
        return $user->isStaff();
    }

    /**
     * Determine whether the user can cancel the invoice.
     */
    public function cancel(User $user, Invoice $invoice): bool
    {
        return $user->isStaff() && $invoice->status !== 'paid';
    }
}