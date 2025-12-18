<?php
// app/Models/Invoice.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Barryvdh\DomPDF\Facade\Pdf;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'subscription_id',
        'transaction_id',
        'invoice_number',
        'type',
        'status',
        'invoice_date',
        'due_date',
        'subtotal',
        'discount_amount',
        'tax_rate',
        'tax_amount',
        'total_amount',
        'line_items',
        'billing_address',
        'notes',
        'pdf_path',
        'sent_at',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'invoice_date' => 'date',
            'due_date' => 'date',
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'line_items' => 'array',
            'billing_address' => 'array',
            'sent_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    // ==================== RELATIONSHIPS ====================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    // ==================== SCOPES ====================

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeUnpaid($query)
    {
        return $query->whereIn('status', ['draft', 'sent']);
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'overdue')
            ->orWhere(function ($q) {
                $q->whereIn('status', ['draft', 'sent'])
                  ->where('due_date', '<', now());
            });
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('invoice_date', now()->month)
            ->whereYear('invoice_date', now()->year);
    }

    // ==================== ACCESSORS ====================

    public function getFormattedTotalAttribute(): string
    {
        return '₹' . number_format($this->total_amount, 2);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'paid' => '<span class="badge bg-success">Paid</span>',
            'sent' => '<span class="badge bg-info">Sent</span>',
            'draft' => '<span class="badge bg-secondary">Draft</span>',
            'overdue' => '<span class="badge bg-danger">Overdue</span>',
            'cancelled' => '<span class="badge bg-warning">Cancelled</span>',
            default => '<span class="badge bg-secondary">Unknown</span>',
        };
    }

    public function getIsOverdueAttribute(): bool
    {
        return in_array($this->status, ['draft', 'sent']) && $this->due_date->isPast();
    }

    public function getPdfUrlAttribute(): ?string
    {
        return $this->pdf_path ? asset('storage/' . $this->pdf_path) : null;
    }

    // ==================== HELPER METHODS ====================

    public static function generateInvoiceNumber(): string
    {
        $prefix = Setting::get('invoice_prefix', 'INV-');
        $year = now()->format('Y');
        $month = now()->format('m');
        
        $lastInvoice = self::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->orderBy('id', 'desc')
            ->first();
        
        $sequence = $lastInvoice ? ((int) substr($lastInvoice->invoice_number, -4)) + 1 : 1;
        
        return $prefix . $year . $month . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    public function markAsPaid(): bool
    {
        return $this->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);
    }

    public function markAsSent(): bool
    {
        return $this->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    public function generatePdf(): string
    {
        $pdf = Pdf::loadView('pdf.invoice', ['invoice' => $this]);
        
        $path = 'invoices/' . $this->invoice_number . '.pdf';
        \Storage::disk('public')->put($path, $pdf->output());
        
        $this->update(['pdf_path' => $path]);
        
        return $path;
    }

    public function getLineItemsForDisplay(): array
    {
        if ($this->line_items) {
            return $this->line_items;
        }

        // Generate default line items from subscription
        if ($this->subscription) {
            return [
                [
                    'description' => $this->subscription->package->name . ' - ' . $this->subscription->package->serviceCategory->name,
                    'quantity' => 1,
                    'rate' => $this->subtotal,
                    'amount' => $this->subtotal,
                ]
            ];
        }

        return [];
    }
}