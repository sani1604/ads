<?php
// app/Models/Transaction.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'subscription_id',
        'transaction_id',
        'type',
        'payment_method',
        'amount',
        'tax_amount',
        'total_amount',
        'currency',
        'status',
        'razorpay_order_id',
        'razorpay_payment_id',
        'razorpay_signature',
        'description',
        'payment_response',
        'meta_data',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'payment_response' => 'array',
            'meta_data' => 'array',
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

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    public function walletTransaction(): HasOne
    {
        return $this->hasOne(WalletTransaction::class);
    }

    // ==================== SCOPES ====================

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year);
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    // ==================== ACCESSORS ====================

    public function getFormattedAmountAttribute(): string
    {
        return '₹' . number_format($this->total_amount, 2);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'completed' => '<span class="badge bg-success">Completed</span>',
            'pending' => '<span class="badge bg-warning">Pending</span>',
            'processing' => '<span class="badge bg-info">Processing</span>',
            'failed' => '<span class="badge bg-danger">Failed</span>',
            'refunded' => '<span class="badge bg-secondary">Refunded</span>',
            default => '<span class="badge bg-secondary">Unknown</span>',
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'subscription' => 'Subscription Payment',
            'wallet_recharge' => 'Wallet Recharge',
            'ad_spend' => 'Ad Spend',
            'refund' => 'Refund',
            'adjustment' => 'Adjustment',
            default => 'Other',
        };
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        return match($this->payment_method) {
            'razorpay' => 'Razorpay',
            'stripe' => 'Stripe',
            'bank_transfer' => 'Bank Transfer',
            'cash' => 'Cash',
            'wallet' => 'Wallet',
            'manual' => 'Manual Entry',
            default => 'Unknown',
        };
    }

    // ==================== HELPER METHODS ====================

    public static function generateTransactionId(): string
    {
        do {
            $id = 'TXN' . now()->format('Ymd') . strtoupper(substr(uniqid(), -6));
        } while (self::where('transaction_id', $id)->exists());

        return $id;
    }

    public function markAsCompleted(string $paymentId = null, string $signature = null): bool
    {
        return $this->update([
            'status' => 'completed',
            'razorpay_payment_id' => $paymentId ?? $this->razorpay_payment_id,
            'razorpay_signature' => $signature ?? $this->razorpay_signature,
            'paid_at' => now(),
        ]);
    }

    public function markAsFailed(array $response = null): bool
    {
        return $this->update([
            'status' => 'failed',
            'payment_response' => $response,
        ]);
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}