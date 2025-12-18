<?php
// app/Console/Commands/GenerateRecurringInvoices.php

namespace App\Console\Commands;

use App\Models\Setting;
use App\Models\Subscription;
use App\Models\Invoice;
use Illuminate\Console\Command;

class GenerateRecurringInvoices extends Command
{
    protected $signature = 'invoices:generate-recurring';
    protected $description = 'Generate invoices for due subscriptions';

    public function handle()
    {
        $this->info('Generating recurring invoices...');

        $subscriptions = Subscription::with(['user', 'package.serviceCategory'])
            ->active()
            ->whereDate('next_billing_date', now()->toDateString())
            ->whereDoesntHave('invoices', function ($q) {
                $q->whereDate('invoice_date', now()->toDateString());
            })
            ->get();

        $count = 0;

        foreach ($subscriptions as $subscription) {
            $this->createInvoiceForSubscription($subscription);
            $count++;
            $this->line("Created invoice for: {$subscription->user->email}");
        }

        $this->info("Generated {$count} invoices.");
    }

    protected function createInvoiceForSubscription(Subscription $subscription): Invoice
    {
        $user = $subscription->user;
        $package = $subscription->package;

        $invoice = Invoice::create([
            'user_id' => $user->id,
            'subscription_id' => $subscription->id,
            'invoice_number' => Invoice::generateInvoiceNumber(),
            'type' => 'subscription',
            'status' => 'sent',
            'invoice_date' => now(),
            'due_date' => now()->addDays(7),
            'subtotal' => $subscription->amount,
            'discount_amount' => 0,
            'tax_rate' => Setting::get('tax_rate', 18),
            'tax_amount' => $subscription->tax_amount,
            'total_amount' => $subscription->total_amount,
            'line_items' => [
                [
                    'description' => $package->name . ' - ' . $package->serviceCategory->name . ' (Renewal)',
                    'quantity' => 1,
                    'rate' => $subscription->amount,
                    'amount' => $subscription->amount,
                ],
            ],
            'billing_address' => [
                'name' => $user->name,
                'company' => $user->company_name,
                'address' => $user->address,
                'city' => $user->city,
                'state' => $user->state,
                'postal_code' => $user->postal_code,
                'gst_number' => $user->gst_number,
            ],
            'sent_at' => now(),
        ]);

        $invoice->generatePdf();

        return $invoice;
    }
}