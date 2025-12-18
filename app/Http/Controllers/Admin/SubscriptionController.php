<?php
// app/Http/Controllers/Admin/SubscriptionController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use App\Services\PaymentService;
use App\Services\ActivityLogService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubscriptionController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->middleware(['auth', 'role:admin,manager']);
        $this->paymentService = $paymentService;
    }

    /**
     * List all subscriptions
     */
    public function index(Request $request)
    {
        $query = Subscription::with(['user', 'package.serviceCategory']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by client
        if ($request->filled('client')) {
            $query->where('user_id', $request->client);
        }

        // Filter by package
        if ($request->filled('package')) {
            $query->where('package_id', $request->package);
        }

        // Filter by expiring soon
        if ($request->filled('expiring')) {
            $days = (int) $request->expiring;
            $query->expiring($days);
        }

        // Search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('subscription_code', 'like', '%' . $request->search . '%')
                    ->orWhereHas('user', fn($q2) => $q2->search($request->search));
            });
        }

        // Date range
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [$request->start_date, $request->end_date . ' 23:59:59']);
        }

        $subscriptions = $query->latest()->paginate(20)->withQueryString();

        // Filter options
        $clients = User::clients()->active()->orderBy('name')->get(['id', 'name', 'company_name']);
        $packages = Package::active()->ordered()->get(['id', 'name']);

        // Stats
        $stats = [
            'total' => Subscription::count(),
            'active' => Subscription::active()->count(),
            'expiring_7_days' => Subscription::expiring(7)->count(),
            'expired' => Subscription::expired()->count(),
            'mrr' => Subscription::active()->sum('total_amount'), // Monthly Recurring Revenue
        ];

        return view('admin.subscriptions.index', compact('subscriptions', 'clients', 'packages', 'stats'));
    }

    /**
     * Show subscription details
     */
    public function show(Subscription $subscription)
    {
        $subscription->load([
            'user',
            'package.serviceCategory',
            'transactions' => fn($q) => $q->latest(),
            'invoices' => fn($q) => $q->latest(),
            'creatives' => fn($q) => $q->latest()->take(5),
            'leads' => fn($q) => $q->latest()->take(10),
        ]);

        // Calculate usage stats
        $usageStats = [
            'creatives_used' => $subscription->getCreativesUsedThisMonth(),
            'creatives_remaining' => $subscription->getCreativesRemainingThisMonth(),
            'leads_this_month' => $subscription->leads()->thisMonth()->count(),
        ];

        return view('admin.subscriptions.show', compact('subscription', 'usageStats'));
    }

    /**
     * Show create form (manual subscription)
     */
    public function create(Request $request)
    {
        $clients = User::clients()
            ->active()
            ->whereDoesntHave('subscriptions', fn($q) => $q->active())
            ->orderBy('name')
            ->get();

        $packages = Package::active()->with('serviceCategory')->ordered()->get();

        $selectedClient = $request->filled('client') ? User::find($request->client) : null;

        return view('admin.subscriptions.create', compact('clients', 'packages', 'selectedClient'));
    }

    /**
     * Store manual subscription
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'package_id' => 'required|exists:packages,id',
            'start_date' => 'required|date',
            'payment_method' => 'required|in:cash,bank_transfer,wallet,manual',
            'payment_reference' => 'nullable|string|max:255',
            'discount_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        $client = User::findOrFail($request->user_id);
        $package = Package::findOrFail($request->package_id);

        // Check if client already has active subscription
        if ($client->hasActiveSubscription()) {
            return back()->with('error', 'Client already has an active subscription.');
        }

        DB::transaction(function () use ($request, $client, $package) {
            // Calculate amounts
            $taxRate = \App\Models\Setting::get('tax_rate', 18) / 100;
            $discountAmount = $request->discount_amount ?? 0;
            $subtotal = $package->price - $discountAmount;
            $taxAmount = $subtotal * $taxRate;
            $totalAmount = $subtotal + $taxAmount;

            $startDate = \Carbon\Carbon::parse($request->start_date);
            $endDate = $startDate->copy()->addDays($package->billing_cycle_days);

            // Create subscription
            $subscription = Subscription::create([
                'user_id' => $client->id,
                'package_id' => $package->id,
                'subscription_code' => Subscription::generateCode(),
                'status' => 'active',
                'amount' => $subtotal,
                'discount_amount' => $discountAmount,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'next_billing_date' => $endDate,
                'billing_cycle_count' => 1,
                'meta_data' => ['notes' => $request->notes],
            ]);

            // Create transaction
            $transaction = Transaction::create([
                'user_id' => $client->id,
                'subscription_id' => $subscription->id,
                'transaction_id' => Transaction::generateTransactionId(),
                'type' => 'subscription',
                'payment_method' => $request->payment_method,
                'amount' => $subtotal,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'currency' => 'INR',
                'status' => 'completed',
                'description' => "Manual subscription: {$package->name}",
                'meta_data' => ['payment_reference' => $request->payment_reference],
                'paid_at' => now(),
            ]);

            // Create invoice
            $this->createInvoice($subscription, $transaction);

            // Log activity
            ActivityLogService::log(
                'subscription_created_manual',
                "Manual subscription created for {$client->name}",
                $subscription,
                ['package' => $package->name, 'amount' => $totalAmount]
            );

            // Notify client
            NotificationService::subscriptionActivated($client, $subscription);
        });

        return redirect()->route('admin.subscriptions.index')
            ->with('success', 'Subscription created successfully.');
    }

    /**
     * Edit subscription
     */
    public function edit(Subscription $subscription)
    {
        $subscription->load(['user', 'package']);
        $packages = Package::active()->with('serviceCategory')->ordered()->get();

        return view('admin.subscriptions.edit', compact('subscription', 'packages'));
    }

    /**
     * Update subscription
     */
    public function update(Request $request, Subscription $subscription)
    {
        $request->validate([
            'status' => 'required|in:pending,active,paused,cancelled,expired',
            'end_date' => 'required|date',
            'next_billing_date' => 'required|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        $metaData = $subscription->meta_data ?? [];
        $metaData['notes'] = $request->notes;
        $metaData['updated_by'] = auth()->user()->name;
        $metaData['updated_at'] = now()->toDateTimeString();

        $subscription->update([
            'status' => $request->status,
            'end_date' => $request->end_date,
            'next_billing_date' => $request->next_billing_date,
            'meta_data' => $metaData,
        ]);

        ActivityLogService::log(
            'subscription_updated',
            "Subscription updated: {$subscription->subscription_code}",
            $subscription
        );

        return redirect()->route('admin.subscriptions.show', $subscription)
            ->with('success', 'Subscription updated successfully.');
    }

    /**
     * Extend subscription
     */
    public function extend(Request $request, Subscription $subscription)
    {
        $request->validate([
            'days' => 'required|integer|min:1|max:365',
            'reason' => 'nullable|string|max:500',
        ]);

        $oldEndDate = $subscription->end_date->copy();
        $newEndDate = $subscription->end_date->addDays($request->days);

        $subscription->update([
            'end_date' => $newEndDate,
            'next_billing_date' => $newEndDate,
        ]);

        ActivityLogService::log(
            'subscription_extended',
            "Subscription extended by {$request->days} days",
            $subscription,
            [
                'old_end_date' => $oldEndDate->format('Y-m-d'),
                'new_end_date' => $newEndDate->format('Y-m-d'),
                'reason' => $request->reason,
            ]
        );

        return back()->with('success', "Subscription extended by {$request->days} days.");
    }

    /**
     * Pause subscription
     */
    public function pause(Request $request, Subscription $subscription)
    {
        if ($subscription->status !== 'active') {
            return back()->with('error', 'Only active subscriptions can be paused.');
        }

        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $subscription->update([
            'status' => 'paused',
            'meta_data' => array_merge($subscription->meta_data ?? [], [
                'paused_at' => now()->toDateTimeString(),
                'paused_reason' => $request->reason,
                'remaining_days' => $subscription->days_remaining,
            ]),
        ]);

        ActivityLogService::log(
            'subscription_paused',
            "Subscription paused",
            $subscription,
            ['reason' => $request->reason]
        );

        return back()->with('success', 'Subscription paused successfully.');
    }

    /**
     * Resume subscription
     */
    public function resume(Subscription $subscription)
    {
        if ($subscription->status !== 'paused') {
            return back()->with('error', 'Only paused subscriptions can be resumed.');
        }

        $remainingDays = $subscription->meta_data['remaining_days'] ?? 0;
        $newEndDate = now()->addDays($remainingDays);

        $subscription->update([
            'status' => 'active',
            'end_date' => $newEndDate,
            'next_billing_date' => $newEndDate,
        ]);

        ActivityLogService::log(
            'subscription_resumed',
            "Subscription resumed",
            $subscription
        );

        return back()->with('success', 'Subscription resumed successfully.');
    }

    /**
     * Cancel subscription
     */
    public function cancel(Request $request, Subscription $subscription)
    {
        if (!$subscription->canCancel()) {
            return back()->with('error', 'This subscription cannot be cancelled.');
        }

        $request->validate([
            'reason' => 'required|string|max:1000',
            'immediate' => 'boolean',
        ]);

        if ($request->boolean('immediate')) {
            $subscription->update([
                'status' => 'cancelled',
                'cancellation_reason' => $request->reason,
                'cancelled_at' => now(),
                'end_date' => now(),
            ]);
        } else {
            // Cancel at end of billing period
            $subscription->update([
                'cancellation_reason' => $request->reason,
                'cancelled_at' => now(),
                'meta_data' => array_merge($subscription->meta_data ?? [], [
                    'scheduled_cancellation' => true,
                ]),
            ]);
        }

        ActivityLogService::log(
            'subscription_cancelled',
            "Subscription cancelled",
            $subscription,
            ['reason' => $request->reason, 'immediate' => $request->boolean('immediate')]
        );

        return back()->with('success', 'Subscription cancelled successfully.');
    }

    /**
     * Renew subscription manually
     */
    public function renew(Request $request, Subscription $subscription)
    {
        $request->validate([
            'payment_method' => 'required|in:cash,bank_transfer,wallet,manual',
            'payment_reference' => 'nullable|string|max:255',
        ]);

        $client = $subscription->user;
        $package = $subscription->package;

        DB::transaction(function () use ($request, $subscription, $client, $package) {
            // Calculate amounts
            $taxRate = \App\Models\Setting::get('tax_rate', 18) / 100;
            $subtotal = $package->price;
            $taxAmount = $subtotal * $taxRate;
            $totalAmount = $subtotal + $taxAmount;

            // Update subscription
            $subscription->update([
                'status' => 'active',
                'amount' => $subtotal,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'start_date' => now(),
                'end_date' => now()->addDays($package->billing_cycle_days),
                'next_billing_date' => now()->addDays($package->billing_cycle_days),
                'billing_cycle_count' => $subscription->billing_cycle_count + 1,
                'cancellation_reason' => null,
                'cancelled_at' => null,
            ]);

            // Create transaction
            $transaction = Transaction::create([
                'user_id' => $client->id,
                'subscription_id' => $subscription->id,
                'transaction_id' => Transaction::generateTransactionId(),
                'type' => 'subscription',
                'payment_method' => $request->payment_method,
                'amount' => $subtotal,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'currency' => 'INR',
                'status' => 'completed',
                'description' => "Subscription renewal: {$package->name}",
                'meta_data' => ['payment_reference' => $request->payment_reference],
                'paid_at' => now(),
            ]);

            // Create invoice
            $this->createInvoice($subscription, $transaction);

            // Notify client
            NotificationService::subscriptionRenewed($client, $subscription);
        });

        ActivityLogService::log(
            'subscription_renewed_manual',
            "Subscription renewed manually",
            $subscription
        );

        return back()->with('success', 'Subscription renewed successfully.');
    }

    /**
     * Change package
     */
    public function changePackage(Request $request, Subscription $subscription)
    {
        $request->validate([
            'package_id' => 'required|exists:packages,id',
            'prorate' => 'boolean',
        ]);

        $newPackage = Package::findOrFail($request->package_id);
        $oldPackage = $subscription->package;

        $subscription->update([
            'package_id' => $newPackage->id,
            'amount' => $newPackage->price,
        ]);

        ActivityLogService::log(
            'subscription_package_changed',
            "Package changed from {$oldPackage->name} to {$newPackage->name}",
            $subscription
        );

        return back()->with('success', 'Package changed successfully.');
    }

    /**
     * Export subscriptions
     */
    public function export(Request $request)
    {
        $query = Subscription::with(['user', 'package.serviceCategory']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [$request->start_date, $request->end_date . ' 23:59:59']);
        }

        $subscriptions = $query->latest()->get();

        $csv = "Code,Client,Email,Package,Category,Status,Amount,Start Date,End Date,Created At\n";

        foreach ($subscriptions as $sub) {
            $csv .= implode(',', [
                $sub->subscription_code,
                '"' . ($sub->user->company_name ?? $sub->user->name) . '"',
                $sub->user->email,
                '"' . $sub->package->name . '"',
                '"' . $sub->package->serviceCategory->name . '"',
                $sub->status,
                $sub->total_amount,
                $sub->start_date->format('Y-m-d'),
                $sub->end_date->format('Y-m-d'),
                $sub->created_at->format('Y-m-d H:i:s'),
            ]) . "\n";
        }

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename=subscriptions_' . now()->format('Y-m-d') . '.csv');
    }

    /**
     * Get expiring subscriptions (AJAX for notifications)
     */
    public function getExpiring(Request $request)
    {
        $days = $request->get('days', 7);

        $subscriptions = Subscription::with(['user', 'package'])
            ->expiring($days)
            ->get();

        return response()->json([
            'count' => $subscriptions->count(),
            'subscriptions' => $subscriptions,
        ]);
    }

    /**
     * Send renewal reminder
     */
    public function sendRenewalReminder(Subscription $subscription)
    {
        NotificationService::subscriptionExpiringSoon($subscription->user, $subscription);

        return back()->with('success', 'Renewal reminder sent to client.');
    }

    /**
     * Create invoice helper
     */
    protected function createInvoice(Subscription $subscription, Transaction $transaction): \App\Models\Invoice
    {
        $user = $subscription->user;
        $package = $subscription->package;

        return \App\Models\Invoice::create([
            'user_id' => $user->id,
            'subscription_id' => $subscription->id,
            'transaction_id' => $transaction->id,
            'invoice_number' => \App\Models\Invoice::generateInvoiceNumber(),
            'type' => 'subscription',
            'status' => 'paid',
            'invoice_date' => now(),
            'due_date' => now(),
            'subtotal' => $subscription->amount,
            'discount_amount' => $subscription->discount_amount,
            'tax_rate' => \App\Models\Setting::get('tax_rate', 18),
            'tax_amount' => $subscription->tax_amount,
            'total_amount' => $subscription->total_amount,
            'line_items' => [
                [
                    'description' => $package->name . ' - ' . $package->serviceCategory->name,
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
            'paid_at' => now(),
        ]);
    }
}