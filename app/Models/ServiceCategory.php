<?php
// app/Models/ServiceCategory.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'color',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    // ==================== RELATIONSHIPS ====================

    public function packages(): HasMany
    {
        return $this->hasMany(Package::class);
    }

    public function creatives(): HasMany
    {
        return $this->hasMany(Creative::class);
    }

    // ==================== SCOPES ====================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    // ==================== ACCESSORS ====================

    public function getActivePackagesCountAttribute(): int
    {
        return $this->packages()->where('is_active', true)->count();
    }

    public function getStartingPriceAttribute(): ?float
    {
        return $this->packages()->where('is_active', true)->min('price');
    }

    public function getFormattedStartingPriceAttribute(): string
    {
        $price = $this->starting_price;
        return $price ? '₹' . number_format($price, 0) : 'Contact Us';
    }
}