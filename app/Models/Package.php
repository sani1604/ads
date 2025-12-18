<?php
// app/Models/Package.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Package extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'service_category_id',
        'industry_id',
        'name',
        'slug',
        'description',
        'short_description',
        'price',
        'original_price',
        'billing_cycle',
        'billing_cycle_days',
        'features',
        'deliverables',
        'max_creatives_per_month',
        'max_revisions',
        'is_featured',
        'is_active',
        'sort_order',
    ];

  protected function casts(): array
{
    return [
        'price'          => 'decimal:2',
        'original_price' => 'decimal:2',
        'features'       => 'array',
        'deliverables' => 'array',
        'is_featured'    => 'boolean',
        'is_active'      => 'boolean',
    ];
}


    // ==================== RELATIONSHIPS ====================

    public function serviceCategory(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class);
    }

    public function industry(): BelongsTo
    {
        return $this->belongsTo(Industry::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    // ==================== SCOPES ====================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('price');
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('service_category_id', $categoryId);
    }

    public function scopeByIndustry($query, $industryId)
    {
        return $query->where(function ($q) use ($industryId) {
            $q->where('industry_id', $industryId)
              ->orWhereNull('industry_id');
        });
    }

    // ==================== ACCESSORS ====================

    public function getFormattedPriceAttribute(): string
    {
        return '₹' . number_format($this->price, 0);
    }

    public function getFormattedOriginalPriceAttribute(): ?string
    {
        return $this->original_price ? '₹' . number_format($this->original_price, 0) : null;
    }

    public function getDiscountPercentageAttribute(): ?int
    {
        if ($this->original_price && $this->original_price > $this->price) {
            return round((($this->original_price - $this->price) / $this->original_price) * 100);
        }
        return null;
    }

    public function getHasDiscountAttribute(): bool
    {
        return $this->original_price && $this->original_price > $this->price;
    }

    public function getBillingCycleLabelAttribute(): string
    {
        return match($this->billing_cycle) {
            'monthly' => 'per month',
            'quarterly' => 'per quarter',
            'yearly' => 'per year',
            default => 'per month',
        };
    }

    public function getPriceWithTaxAttribute(): float
    {
        $taxRate = Setting::get('tax_rate', 18) / 100;
        return $this->price * (1 + $taxRate);
    }

    public function getTaxAmountAttribute(): float
    {
        $taxRate = Setting::get('tax_rate', 18) / 100;
        return $this->price * $taxRate;
    }

    // ==================== HELPER METHODS ====================

    public function getActiveSubscribersCount(): int
    {
        return $this->subscriptions()->where('status', 'active')->count();
    }

    public function isPopular(): bool
    {
        return $this->getActiveSubscribersCount() > 10;
    }
}