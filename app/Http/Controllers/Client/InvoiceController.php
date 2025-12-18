<?php
// app/Http/Controllers/Client/InvoiceController.php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'onboarding']);
    }

    /**
     * List all invoices
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = $user->invoices()->with('subscription.package');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by date range
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('invoice_date', [$request->start_date, $request->end_date]);
        }

        $invoices = $query->latest()->paginate(15);

        $stats = [
            'total' => $user->invoices()->count(),
            'paid' => $user->invoices()->paid()->count(),
            'total_amount' => $user->invoices()->paid()->sum('total_amount'),
        ];

        return view('client.invoices.index', compact('invoices', 'stats'));
    }

    /**
     * Show invoice details
     */
    public function show(Invoice $invoice)
    {
        $this->authorize('view', $invoice);

        return view('client.invoices.show', compact('invoice'));
    }

    /**
     * Download invoice PDF
     */
    public function download(Invoice $invoice)
    {
        $this->authorize('view', $invoice);

        // Generate PDF if not exists
        if (!$invoice->pdf_path || !\Storage::disk('public')->exists($invoice->pdf_path)) {
            $invoice->generatePdf();
        }

        return response()->download(
            storage_path('app/public/' . $invoice->pdf_path),
            $invoice->invoice_number . '.pdf'
        );
    }

    /**
     * View invoice PDF in browser
     */
    public function view(Invoice $invoice)
    {
        $this->authorize('view', $invoice);

        $pdf = Pdf::loadView('pdf.invoice', ['invoice' => $invoice]);

        return $pdf->stream($invoice->invoice_number . '.pdf');
    }
}