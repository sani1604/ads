<?php
// app/Models/WalletTransaction.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WalletTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'transaction_id',
        'type',
        'amount',
        'balance_before',
        'balance_after',
        'reference_type',
        'reference_id',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'balance_before' => 'decimal:2',
            'balance_after' => 'decimal:2',
        ];
    }

    // ==================== RELATIONSHIPS ====================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    // ==================== SCOPES ====================

    public function scopeCredits($query)
    {
        return $query->where('type', 'credit');
    }

    public function scopeDebits($query)
    {
        return $query->where('type', 'debit');
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year);
    }

    // ==================== ACCESSORS ====================

    public function getFormattedAmountAttribute(): string
    {
        $prefix = $this->type === 'credit' ? '+' : '-';
        return $prefix . '₹' . number_format($this->amount, 2);
    }

    public function getAmountColorAttribute(): string
    {
        return $this->type === 'credit' ? 'text-success' : 'text-danger';
    }

    public function getTypeBadgeAttribute(): string
    {
        return $this->type === 'credit' 
            ? '<span class="badge bg-success">Credit</span>'
            : '<span class="badge bg-danger">Debit</span>';
    }
}