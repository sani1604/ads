<?php
// app/Models/Lead.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lead extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'subscription_id',
        'lead_id',
        'name',
        'email',
        'phone',
        'alternate_phone',
        'source',
        'campaign_name',
        'ad_name',
        'adset_name',
        'form_name',
        'status',
        'quality',
        'custom_fields',
        'notes',
        'ad_spend',
        'city',
        'state',
        'lead_created_at',
        'contacted_at',
    ];

    protected function casts(): array
    {
        return [
            'custom_fields' => 'array',
            'ad_spend' => 'decimal:2',
            'lead_created_at' => 'datetime',
            'contacted_at' => 'datetime',
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

    // ==================== SCOPES ====================

    public function scopeNew($query)
    {
        return $query->where('status', 'new');
    }

    public function scopeContacted($query)
    {
        return $query->where('status', 'contacted');
    }

    public function scopeQualified($query)
    {
        return $query->where('status', 'qualified');
    }

    public function scopeConverted($query)
    {
        return $query->where('status', 'converted');
    }

    public function scopeHot($query)
    {
        return $query->where('quality', 'hot');
    }

    public function scopeFromSource($query, $source)
    {
        return $query->where('source', $source);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year);
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('created_at', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%");
        });
    }

    // ==================== ACCESSORS ====================

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'new' => '<span class="badge bg-primary">New</span>',
            'contacted' => '<span class="badge bg-info">Contacted</span>',
            'qualified' => '<span class="badge bg-success">Qualified</span>',
            'converted' => '<span class="badge bg-success">Converted</span>',
            'lost' => '<span class="badge bg-danger">Lost</span>',
            'spam' => '<span class="badge bg-secondary">Spam</span>',
            default => '<span class="badge bg-secondary">Unknown</span>',
        };
    }

    public function getQualityBadgeAttribute(): ?string
    {
        return match($this->quality) {
            'hot' => '<span class="badge bg-danger">🔥 Hot</span>',
            'warm' => '<span class="badge bg-warning">Warm</span>',
            'cold' => '<span class="badge bg-info">Cold</span>',
            default => null,
        };
    }

    public function getSourceIconAttribute(): string
    {
        return match($this->source) {
            'facebook' => 'fab fa-facebook text-primary',
            'instagram' => 'fab fa-instagram text-danger',
            'google' => 'fab fa-google text-success',
            'linkedin' => 'fab fa-linkedin text-info',
            'website' => 'fas fa-globe text-secondary',
            default => 'fas fa-user text-muted',
        };
    }

    public function getFormattedPhoneAttribute(): string
    {
        if (!$this->phone) return 'N/A';
        return preg_replace('/(\d{2})(\d{5})(\d{5})/', '+$1 $2 $3', $this->phone);
    }

    // ==================== HELPER METHODS ====================

    public static function generateLeadId(): string
    {
        do {
            $id = 'LEAD-' . strtoupper(substr(uniqid(), -8));
        } while (self::where('lead_id', $id)->exists());

        return $id;
    }

    public function markAsContacted(): bool
    {
        return $this->update([
            'status' => 'contacted',
            'contacted_at' => now(),
        ]);
    }

    public function markAsQualified(): bool
    {
        return $this->update(['status' => 'qualified']);
    }

    public function markAsConverted(): bool
    {
        return $this->update(['status' => 'converted']);
    }

    public function markAsLost(): bool
    {
        return $this->update(['status' => 'lost']);
    }

    public function markAsSpam(): bool
    {
        return $this->update(['status' => 'spam']);
    }

    public function setQuality(string $quality): bool
    {
        return $this->update(['quality' => $quality]);
    }
}