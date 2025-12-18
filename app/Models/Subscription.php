<?php
// app/Models/Subscription.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Subscription extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'package_id',
        'subscription_code',
        'status',
        'amount',
        'discount_amount',
        'tax_amount',
        'total_amount',
        'start_date',
        'end_date',
        'next_billing_date',
        'billing_cycle_count',
        'razorpay_subscription_id',
        'razorpay_plan_id',
        'meta_data',
        'cancellation_reason',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'start_date' => 'date',
            'end_date' => 'date',
            'next_billing_date' => 'date',
            'meta_data' => 'array',
            'cancelled_at' => 'datetime',
        ];
    }

    // ==================== RELATIONSHIPS ====================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class)->withTrashed();
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function creatives(): HasMany
    {
        return $this->hasMany(Creative::class);
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    public function campaignReports(): HasMany
    {
        return $this->hasMany(CampaignReport::class);
    }

    // ==================== SCOPES ====================

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'expired');
    }

    public function scopeExpiring($query, $days = 7)
    {
        return $query->where('status', 'active')
            ->whereBetween('end_date', [now(), now()->addDays($days)]);
    }

    public function scopeDueTomorrow($query)
    {
        return $query->where('status', 'active')
            ->whereDate('next_billing_date', now()->addDay());
    }

    // ==================== ACCESSORS ====================

    public function getFormattedAmountAttribute(): string
    {
        return '₹' . number_format($this->total_amount, 2);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'active' => '<span class="badge bg-success">Active</span>',
            'pending' => '<span class="badge bg-warning">Pending</span>',
            'paused' => '<span class="badge bg-info">Paused</span>',
            'cancelled' => '<span class="badge bg-danger">Cancelled</span>',
            'expired' => '<span class="badge bg-secondary">Expired</span>',
            default => '<span class="badge bg-secondary">Unknown</span>',
        };
    }

    public function getDaysRemainingAttribute(): int
    {
        if ($this->end_date->isPast()) {
            return 0;
        }
        return now()->diffInDays($this->end_date);
    }

    public function getDaysUntilBillingAttribute(): int
    {
        if ($this->next_billing_date->isPast()) {
            return 0;
        }
        return now()->diffInDays($this->next_billing_date);
    }

    public function getIsExpiringAttribute(): bool
    {
        return $this->status === 'active' && $this->days_remaining <= 7;
    }

    // ==================== HELPER METHODS ====================

    public function isActive(): bool
    {
        return $this->status === 'active' && $this->end_date->isFuture();
    }

    public function canRenew(): bool
    {
        return in_array($this->status, ['active', 'expired']);
    }

    public function canCancel(): bool
    {
        return $this->status === 'active';
    }

    public function cancel(string $reason = null): bool
    {
        $this->update([
            'status' => 'cancelled',
            'cancellation_reason' => $reason,
            'cancelled_at' => now(),
        ]);

        return true;
    }

    public function renew(): bool
    {
        $package = $this->package;
        
        $this->update([
            'status' => 'active',
            'start_date' => now(),
            'end_date' => now()->addDays($package->billing_cycle_days),
            'next_billing_date' => now()->addDays($package->billing_cycle_days),
            'billing_cycle_count' => $this->billing_cycle_count + 1,
        ]);

        return true;
    }

    public static function generateCode(): string
    {
        do {
            $code = 'SUB-' . strtoupper(uniqid());
        } while (self::where('subscription_code', $code)->exists());

        return $code;
    }

    public function getCreativesUsedThisMonth(): int
    {
        return $this->creatives()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
    }

    public function getCreativesRemainingThisMonth(): int
    {
        $used = $this->getCreativesUsedThisMonth();
        $max = $this->package->max_creatives_per_month;
        return max(0, $max - $used);
    }
}