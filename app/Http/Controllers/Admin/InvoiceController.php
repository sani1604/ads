<?php
// app/Http/Controllers/Admin/InvoiceController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Setting;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class InvoiceController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin,manager']);
    }

    /**
     * List all invoices
     */
    public function index(Request $request)
    {
        $query = Invoice::with(['user', 'subscription.package', 'transaction']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by client
        if ($request->filled('client')) {
            $query->where('user_id', $request->client);
        }

        // Filter by date range
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('invoice_date', [$request->start_date, $request->end_date]);
        }

        // Search by invoice number
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('invoice_number', 'like', '%' . $request->search . '%')
                    ->orWhereHas('user', fn($q2) => $q2->search($request->search));
            });
        }

        // Overdue filter
        if ($request->boolean('overdue')) {
            $query->overdue();
        }

        $invoices = $query->latest()->paginate(20)->withQueryString();

        // Filter options
        $clients = User::clients()->orderBy('name')->get(['id', 'name', 'company_name']);

        // Stats
        $stats = [
            'total' => Invoice::count(),
            'paid' => Invoice::paid()->count(),
            'unpaid' => Invoice::unpaid()->count(),
            'overdue' => Invoice::overdue()->count(),
            'total_revenue' => Invoice::paid()->sum('total_amount'),
            'pending_amount' => Invoice::unpaid()->sum('total_amount'),
            'this_month_revenue' => Invoice::paid()->thisMonth()->sum('total_amount'),
        ];

        return view('admin.invoices.index', compact('invoices', 'clients', 'stats'));
    }

    /**
     * Show invoice details
     */
    public function show(Invoice $invoice)
    {
        $invoice->load(['user', 'subscription.package.serviceCategory', 'transaction']);

        return view('admin.invoices.show', compact('invoice'));
    }

    /**
     * Show create form
     */
    public function create(Request $request)
    {
        $clients = User::clients()->active()->orderBy('name')->get();
        $selectedClient = $request->filled('client') ? User::find($request->client) : null;

        return view('admin.invoices.create', compact('clients', 'selectedClient'));
    }

    /**
     * Store new invoice (manual)
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'type' => 'required|in:subscription,wallet_recharge,one_time',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:invoice_date',
            'line_items' => 'required|array|min:1',
            'line_items.*.description' => 'required|string|max:255',
            'line_items.*.quantity' => 'required|numeric|min:1',
            'line_items.*.rate' => 'required|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
            'send_email' => 'boolean',
        ]);

        $client = User::findOrFail($request->user_id);

        // Calculate totals
        $subtotal = 0;
        $lineItems = [];

        foreach ($request->line_items as $item) {
            $amount = $item['quantity'] * $item['rate'];
            $subtotal += $amount;
            $lineItems[] = [
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'rate' => $item['rate'],
                'amount' => $amount,
            ];
        }

        $discountAmount = $request->discount_amount ?? 0;
        $taxableAmount = $subtotal - $discountAmount;
        $taxRate = Setting::get('tax_rate', 18);
        $taxAmount = $taxableAmount * ($taxRate / 100);
        $totalAmount = $taxableAmount + $taxAmount;

        $invoice = Invoice::create([
            'user_id' => $client->id,
            'invoice_number' => Invoice::generateInvoiceNumber(),
            'type' => $request->type,
            'status' => 'sent',
            'invoice_date' => $request->invoice_date,
            'due_date' => $request->due_date,
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
            'line_items' => $lineItems,
            'billing_address' => [
                'name' => $client->name,
                'company' => $client->company_name,
                'address' => $client->address,
                'city' => $client->city,
                'state' => $client->state,
                'postal_code' => $client->postal_code,
                'gst_number' => $client->gst_number,
            ],
            'notes' => $request->notes,
            'sent_at' => now(),
        ]);

        // Generate PDF
        $invoice->generatePdf();

        // Send email if requested
        if ($request->boolean('send_email')) {
            $this->sendInvoiceEmail($invoice);
        }

        return redirect()->route('admin.invoices.show', $invoice)
            ->with('success', 'Invoice created successfully.');
    }

    /**
     * Edit invoice
     */
    public function edit(Invoice $invoice)
    {
        if ($invoice->status === 'paid') {
            return back()->with('error', 'Paid invoices cannot be edited.');
        }

        $invoice->load('user');

        return view('admin.invoices.edit', compact('invoice'));
    }

    /**
     * Update invoice
     */
    public function update(Request $request, Invoice $invoice)
    {
        if ($invoice->status === 'paid') {
            return back()->with('error', 'Paid invoices cannot be edited.');
        }

        $request->validate([
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:invoice_date',
            'line_items' => 'required|array|min:1',
            'line_items.*.description' => 'required|string|max:255',
            'line_items.*.quantity' => 'required|numeric|min:1',
            'line_items.*.rate' => 'required|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Calculate totals
        $subtotal = 0;
        $lineItems = [];

        foreach ($request->line_items as $item) {
            $amount = $item['quantity'] * $item['rate'];
            $subtotal += $amount;
            $lineItems[] = [
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'rate' => $item['rate'],
                'amount' => $amount,
            ];
        }

        $discountAmount = $request->discount_amount ?? 0;
        $taxableAmount = $subtotal - $discountAmount;
        $taxRate = Setting::get('tax_rate', 18);
        $taxAmount = $taxableAmount * ($taxRate / 100);
        $totalAmount = $taxableAmount + $taxAmount;

        $invoice->update([
            'invoice_date' => $request->invoice_date,
            'due_date' => $request->due_date,
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
            'line_items' => $lineItems,
            'notes' => $request->notes,
        ]);

        // Regenerate PDF
        $invoice->generatePdf();

        return redirect()->route('admin.invoices.show', $invoice)
            ->with('success', 'Invoice updated successfully.');
    }

    /**
     * Mark invoice as paid
     */
    public function markAsPaid(Request $request, Invoice $invoice)
    {
        if ($invoice->status === 'paid') {
            return back()->with('error', 'Invoice is already paid.');
        }

        $request->validate([
            'payment_method' => 'required|in:razorpay,stripe,bank_transfer,cash,wallet,manual',
            'payment_reference' => 'nullable|string|max:255',
            'paid_at' => 'nullable|date',
        ]);

        // Create transaction
        $transaction = Transaction::create([
            'user_id' => $invoice->user_id,
            'subscription_id' => $invoice->subscription_id,
            'transaction_id' => Transaction::generateTransactionId(),
            'type' => $invoice->type,
            'payment_method' => $request->payment_method,
            'amount' => $invoice->subtotal - $invoice->discount_amount,
            'tax_amount' => $invoice->tax_amount,
            'total_amount' => $invoice->total_amount,
            'currency' => 'INR',
            'status' => 'completed',
            'description' => "Payment for Invoice #{$invoice->invoice_number}",
            'meta_data' => ['payment_reference' => $request->payment_reference],
            'paid_at' => $request->paid_at ?? now(),
        ]);

        $invoice->update([
            'status' => 'paid',
            'transaction_id' => $transaction->id,
            'paid_at' => $request->paid_at ?? now(),
        ]);

        return back()->with('success', 'Invoice marked as paid.');
    }

    /**
     * Mark invoice as sent
     */
    public function markAsSent(Invoice $invoice)
    {
        $invoice->markAsSent();

        return back()->with('success', 'Invoice marked as sent.');
    }

    /**
     * Cancel invoice
     */
    public function cancel(Request $request, Invoice $invoice)
    {
        if ($invoice->status === 'paid') {
            return back()->with('error', 'Paid invoices cannot be cancelled.');
        }

        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $invoice->update([
            'status' => 'cancelled',
            'notes' => $invoice->notes . "\n\nCancelled: " . ($request->reason ?? 'No reason provided'),
        ]);

        return back()->with('success', 'Invoice cancelled.');
    }

    /**
     * Send invoice email
     */
    public function sendEmail(Invoice $invoice)
    {
        $this->sendInvoiceEmail($invoice);

        $invoice->update(['sent_at' => now()]);

        if ($invoice->status === 'draft') {
            $invoice->update(['status' => 'sent']);
        }

        return back()->with('success', 'Invoice email sent to client.');
    }

    /**
     * Download invoice PDF
     */
    public function download(Invoice $invoice)
    {
        // Generate PDF if not exists
        if (!$invoice->pdf_path || !\Storage::disk('public')->exists($invoice->pdf_path)) {
            $invoice->generatePdf();
            $invoice->refresh();
        }

        return response()->download(
            storage_path('app/public/' . $invoice->pdf_path),
            $invoice->invoice_number . '.pdf'
        );
    }

    /**
     * View invoice PDF in browser
     */
    public function viewPdf(Invoice $invoice)
    {
        $invoice->load(['user', 'subscription.package']);

        $pdf = Pdf::loadView('pdf.invoice', ['invoice' => $invoice]);

        return $pdf->stream($invoice->invoice_number . '.pdf');
    }

    /**
     * Regenerate PDF
     */
    public function regeneratePdf(Invoice $invoice)
    {
        $invoice->generatePdf();

        return back()->with('success', 'Invoice PDF regenerated.');
    }

    /**
     * Delete invoice
     */
    public function destroy(Invoice $invoice)
    {
        if ($invoice->status === 'paid') {
            return back()->with('error', 'Paid invoices cannot be deleted.');
        }

        // Delete PDF file
        if ($invoice->pdf_path && \Storage::disk('public')->exists($invoice->pdf_path)) {
            \Storage::disk('public')->delete($invoice->pdf_path);
        }

        $invoice->delete();

        return redirect()->route('admin.invoices.index')
            ->with('success', 'Invoice deleted.');
    }

    /**
     * Bulk send invoices
     */
    public function bulkSend(Request $request)
    {
        $request->validate([
            'invoice_ids' => 'required|array|min:1',
            'invoice_ids.*' => 'exists:invoices,id',
        ]);

        $count = 0;
        foreach ($request->invoice_ids as $id) {
            $invoice = Invoice::find($id);
            if ($invoice && $invoice->status !== 'paid') {
                $this->sendInvoiceEmail($invoice);
                $invoice->update(['sent_at' => now(), 'status' => 'sent']);
                $count++;
            }
        }

        return back()->with('success', "{$count} invoices sent.");
    }

    /**
     * Export invoices
     */
    public function export(Request $request)
    {
        $query = Invoice::with(['user', 'subscription.package']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('invoice_date', [$request->start_date, $request->end_date]);
        }

        $invoices = $query->latest()->get();

        $csv = "Invoice Number,Client,Email,Type,Status,Subtotal,Tax,Total,Invoice Date,Due Date,Paid At\n";

        foreach ($invoices as $invoice) {
            $csv .= implode(',', [
                $invoice->invoice_number,
                '"' . ($invoice->user->company_name ?? $invoice->user->name) . '"',
                $invoice->user->email,
                $invoice->type,
                $invoice->status,
                $invoice->subtotal,
                $invoice->tax_amount,
                $invoice->total_amount,
                $invoice->invoice_date->format('Y-m-d'),
                $invoice->due_date->format('Y-m-d'),
                $invoice->paid_at ? $invoice->paid_at->format('Y-m-d') : '',
            ]) . "\n";
        }

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename=invoices_' . now()->format('Y-m-d') . '.csv');
    }

    /**
     * Generate recurring invoices
     */
    public function generateRecurring()
    {
        $subscriptions = Subscription::active()
            ->whereDate('next_billing_date', '<=', now())
            ->with(['user', 'package.serviceCategory'])
            ->get();

        $count = 0;

        foreach ($subscriptions as $subscription) {
            // Create invoice for due subscription
            $invoice = Invoice::create([
                'user_id' => $subscription->user_id,
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
                        'description' => $subscription->package->name . ' - ' . $subscription->package->serviceCategory->name,
                        'quantity' => 1,
                        'rate' => $subscription->amount,
                        'amount' => $subscription->amount,
                    ],
                ],
                'billing_address' => [
                    'name' => $subscription->user->name,
                    'company' => $subscription->user->company_name,
                    'address' => $subscription->user->address,
                    'city' => $subscription->user->city,
                    'state' => $subscription->user->state,
                    'postal_code' => $subscription->user->postal_code,
                    'gst_number' => $subscription->user->gst_number,
                ],
                'sent_at' => now(),
            ]);

            $invoice->generatePdf();
            $this->sendInvoiceEmail($invoice);

            $count++;
        }

        return back()->with('success', "{$count} recurring invoices generated.");
    }

    /**
     * Helper: Send invoice email
     */
    protected function sendInvoiceEmail(Invoice $invoice): void
    {
        // Generate PDF if needed
        if (!$invoice->pdf_path || !\Storage::disk('public')->exists($invoice->pdf_path)) {
            $invoice->generatePdf();
            $invoice->refresh();
        }

        Mail::send('emails.invoice', ['invoice' => $invoice], function ($message) use ($invoice) {
            $message->to($invoice->user->email, $invoice->user->name)
                ->subject('Invoice #' . $invoice->invoice_number . ' from ' . Setting::get('site_name', 'Agency Portal'))
                ->attach(storage_path('app/public/' . $invoice->pdf_path), [
                    'as' => $invoice->invoice_number . '.pdf',
                    'mime' => 'application/pdf',
                ]);
        });
    }
}