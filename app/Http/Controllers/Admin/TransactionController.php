<?php
// app/Http/Controllers/Admin/TransactionController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\User;
use App\Services\ActivityLogService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin,manager']);
    }

    /**
     * List all transactions
     */
    public function index(Request $request)
    {
        $query = Transaction::with(['user', 'subscription.package', 'invoice']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by payment method
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        // Filter by client
        if ($request->filled('client')) {
            $query->where('user_id', $request->client);
        }

        // Filter by date range
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59',
            ]);
        }

        // Search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('transaction_id', 'like', '%' . $request->search . '%')
                    ->orWhere('razorpay_payment_id', 'like', '%' . $request->search . '%')
                    ->orWhereHas('user', fn($q2) => $q2->search($request->search));
            });
        }

        $transactions = $query->latest()->paginate(25)->withQueryString();

        // Filter options
        $clients = User::clients()->orderBy('name')->get(['id', 'name', 'company_name']);

        // Stats
        $stats = [
            'total_transactions' => Transaction::count(),
            'completed' => Transaction::completed()->count(),
            'pending' => Transaction::pending()->count(),
            'failed' => Transaction::failed()->count(),
            'total_revenue' => Transaction::completed()->sum('total_amount'),
            'today_revenue' => Transaction::completed()->whereDate('created_at', today())->sum('total_amount'),
            'this_month_revenue' => Transaction::completed()->thisMonth()->sum('total_amount'),
        ];

        // Payment method breakdown
        $paymentMethodBreakdown = Transaction::completed()
            ->selectRaw('payment_method, SUM(total_amount) as total, COUNT(*) as count')
            ->groupBy('payment_method')
            ->get()
            ->keyBy('payment_method')
            ->toArray();

        return view('admin.transactions.index', compact(
            'transactions',
            'clients',
            'stats',
            'paymentMethodBreakdown'
        ));
    }

    /**
     * Show transaction details
     */
    public function show(Transaction $transaction)
    {
        $transaction->load([
            'user',
            'subscription.package.serviceCategory',
            'invoice',
            'walletTransaction',
        ]);

        return view('admin.transactions.show', compact('transaction'));
    }

    /**
     * Create manual transaction
     */
    public function create(Request $request)
    {
        $clients = User::clients()->active()->orderBy('name')->get();
        $selectedClient = $request->filled('client') ? User::find($request->client) : null;

        return view('admin.transactions.create', compact('clients', 'selectedClient'));
    }

    /**
     * Store manual transaction
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'type' => 'required|in:subscription,wallet_recharge,ad_spend,adjustment',
            'payment_method' => 'required|in:bank_transfer,cash,manual',
            'amount' => 'required|numeric|min:1',
            'description' => 'required|string|max:500',
            'payment_reference' => 'nullable|string|max:255',
            'paid_at' => 'nullable|date',
            'credit_wallet' => 'boolean',
        ]);

        $client = User::findOrFail($request->user_id);

        // Calculate tax
        $taxRate = \App\Models\Setting::get('tax_rate', 18) / 100;
        $taxAmount = $request->amount * $taxRate;
        $totalAmount = $request->amount + $taxAmount;

        $transaction = Transaction::create([
            'user_id' => $client->id,
            'transaction_id' => Transaction::generateTransactionId(),
            'type' => $request->type,
            'payment_method' => $request->payment_method,
            'amount' => $request->amount,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
            'currency' => 'INR',
            'status' => 'completed',
            'description' => $request->description,
            'meta_data' => [
                'payment_reference' => $request->payment_reference,
                'created_by' => auth()->user()->name,
            ],
            'paid_at' => $request->paid_at ?? now(),
        ]);

        // Credit wallet if requested
        if ($request->boolean('credit_wallet') && in_array($request->type, ['wallet_recharge', 'adjustment'])) {
            $client->creditWallet(
                $request->amount,
                $request->description,
                'transaction',
                $transaction->id
            );
        }

        ActivityLogService::log(
            'transaction_created_manual',
            "Manual transaction created: {$transaction->transaction_id}",
            $transaction,
            ['amount' => $totalAmount, 'type' => $request->type]
        );

        return redirect()->route('admin.transactions.show', $transaction)
            ->with('success', 'Transaction created successfully.');
    }

    /**
     * Update transaction status
     */
    public function updateStatus(Request $request, Transaction $transaction)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,completed,failed,refunded',
        ]);

        $oldStatus = $transaction->status;
        $newStatus = $request->status;

        $transaction->update([
            'status' => $newStatus,
            'paid_at' => $newStatus === 'completed' ? now() : $transaction->paid_at,
        ]);

        // Handle wallet credit on completion
        if ($oldStatus !== 'completed' && $newStatus === 'completed') {
            if ($transaction->type === 'wallet_recharge') {
                $transaction->user->creditWallet(
                    $transaction->amount,
                    'Wallet recharge - Payment confirmed',
                    'transaction',
                    $transaction->id
                );
            }
        }

        // Handle refund
        if ($newStatus === 'refunded' && $oldStatus === 'completed') {
            if ($transaction->type === 'wallet_recharge') {
                // Debit wallet on refund
                try {
                    $transaction->user->debitWallet(
                        $transaction->amount,
                        'Refund - ' . $transaction->transaction_id,
                        'transaction',
                        $transaction->id
                    );
                } catch (\Exception $e) {
                    // Insufficient balance, just log
                }
            }
        }

        ActivityLogService::log(
            'transaction_status_updated',
            "Transaction status changed from {$oldStatus} to {$newStatus}",
            $transaction
        );

        return back()->with('success', 'Transaction status updated.');
    }

    /**
     * Process refund
     */
    public function refund(Request $request, Transaction $transaction)
    {
        if ($transaction->status !== 'completed') {
            return back()->with('error', 'Only completed transactions can be refunded.');
        }

        $request->validate([
            'amount' => 'required|numeric|min:1|max:' . $transaction->total_amount,
            'reason' => 'required|string|max:500',
        ]);

        $isFullRefund = $request->amount >= $transaction->total_amount;

        // Create refund transaction
        $refundTransaction = Transaction::create([
            'user_id' => $transaction->user_id,
            'subscription_id' => $transaction->subscription_id,
            'transaction_id' => Transaction::generateTransactionId(),
            'type' => 'refund',
            'payment_method' => $transaction->payment_method,
            'amount' => -$request->amount,
            'tax_amount' => 0,
            'total_amount' => -$request->amount,
            'currency' => 'INR',
            'status' => 'completed',
            'description' => "Refund for {$transaction->transaction_id}: {$request->reason}",
            'meta_data' => [
                'original_transaction_id' => $transaction->id,
                'refund_reason' => $request->reason,
                'refunded_by' => auth()->user()->name,
            ],
            'paid_at' => now(),
        ]);

        // Update original transaction
        if ($isFullRefund) {
            $transaction->update(['status' => 'refunded']);
        } else {
            $transaction->update([
                'meta_data' => array_merge($transaction->meta_data ?? [], [
                    'partial_refund' => $request->amount,
                    'refund_transaction_id' => $refundTransaction->id,
                ]),
            ]);
        }

        ActivityLogService::log(
            'transaction_refunded',
            "Transaction refunded: ₹{$request->amount}",
            $transaction,
            ['refund_amount' => $request->amount, 'reason' => $request->reason]
        );

        return back()->with('success', 'Refund processed successfully.');
    }

    /**
     * Export transactions
     */
    public function export(Request $request)
    {
        $query = Transaction::with(['user', 'subscription.package']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59',
            ]);
        }

        $transactions = $query->latest()->get();

        $csv = "Transaction ID,Client,Email,Type,Payment Method,Amount,Tax,Total,Status,Date\n";

        foreach ($transactions as $txn) {
            $csv .= implode(',', [
                $txn->transaction_id,
                '"' . ($txn->user->company_name ?? $txn->user->name) . '"',
                $txn->user->email,
                $txn->type,
                $txn->payment_method,
                $txn->amount,
                $txn->tax_amount,
                $txn->total_amount,
                $txn->status,
                $txn->created_at->format('Y-m-d H:i:s'),
            ]) . "\n";
        }

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename=transactions_' . now()->format('Y-m-d') . '.csv');
    }

    /**
     * Get revenue analytics (AJAX)
     */
    public function analytics(Request $request)
    {
        $period = $request->get('period', '30days');
        $endDate = now();

        switch ($period) {
            case '7days':
                $startDate = now()->subDays(7);
                $groupBy = 'DATE(created_at)';
                $dateFormat = 'M d';
                break;
            case '30days':
                $startDate = now()->subDays(30);
                $groupBy = 'DATE(created_at)';
                $dateFormat = 'M d';
                break;
            case '90days':
                $startDate = now()->subDays(90);
                $groupBy = 'YEARWEEK(created_at)';
                $dateFormat = 'W';
                break;
            case '12months':
                $startDate = now()->subMonths(12);
                $groupBy = 'DATE_FORMAT(created_at, "%Y-%m")';
                $dateFormat = 'M Y';
                break;
            default:
                $startDate = now()->subDays(30);
                $groupBy = 'DATE(created_at)';
                $dateFormat = 'M d';
        }

        // Revenue over time
        $revenueData = Transaction::completed()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw("{$groupBy} as period, SUM(total_amount) as total")
            ->groupBy('period')
            ->orderBy('period')
            ->pluck('total', 'period')
            ->toArray();

        // Revenue by type
        $typeData = Transaction::completed()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('type, SUM(total_amount) as total')
            ->groupBy('type')
            ->pluck('total', 'type')
            ->toArray();

        // Revenue by payment method
        $methodData = Transaction::completed()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('payment_method, SUM(total_amount) as total')
            ->groupBy('payment_method')
            ->pluck('total', 'payment_method')
            ->toArray();

        // Summary stats
        $summary = [
            'total_revenue' => array_sum($revenueData),
            'transaction_count' => Transaction::completed()
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count(),
            'average_transaction' => Transaction::completed()
                ->whereBetween('created_at', [$startDate, $endDate])
                ->avg('total_amount') ?? 0,
        ];

        return response()->json([
            'revenue' => $revenueData,
            'by_type' => $typeData,
            'by_method' => $methodData,
            'summary' => $summary,
        ]);
    }
}