<?php
// app/Models/CampaignReport.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampaignReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'subscription_id',
        'platform',
        'campaign_id',
        'campaign_name',
        'report_date',
        'impressions',
        'reach',
        'clicks',
        'link_clicks',
        'ctr',
        'cpc',
        'cpm',
        'cpl',
        'leads',
        'conversions',
        'spend',
        'video_views',
        'engagements',
        'additional_metrics',
    ];

    protected function casts(): array
    {
        return [
            'report_date' => 'date',
            'ctr' => 'decimal:4',
            'cpc' => 'decimal:2',
            'cpm' => 'decimal:2',
            'cpl' => 'decimal:2',
            'spend' => 'decimal:2',
            'additional_metrics' => 'array',
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

    public function scopeForPlatform($query, $platform)
    {
        return $query->where('platform', $platform);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('report_date', now()->month)
            ->whereYear('report_date', now()->year);
    }

    public function scopeLastMonth($query)
    {
        return $query->whereMonth('report_date', now()->subMonth()->month)
            ->whereYear('report_date', now()->subMonth()->year);
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('report_date', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('report_date', [$startDate, $endDate]);
    }

    // ==================== ACCESSORS ====================

    public function getFormattedSpendAttribute(): string
    {
        return '₹' . number_format($this->spend, 2);
    }

    public function getFormattedCtrAttribute(): string
    {
        return number_format($this->ctr, 2) . '%';
    }

    public function getFormattedCpcAttribute(): string
    {
        return '₹' . number_format($this->cpc, 2);
    }

    public function getFormattedCplAttribute(): string
    {
        return $this->cpl > 0 ? '₹' . number_format($this->cpl, 2) : 'N/A';
    }

    public function getPlatformIconAttribute(): string
    {
        return match($this->platform) {
            'facebook' => 'fab fa-facebook text-primary',
            'instagram' => 'fab fa-instagram text-danger',
            'google' => 'fab fa-google text-warning',
            'linkedin' => 'fab fa-linkedin text-info',
            default => 'fas fa-ad',
        };
    }

    // ==================== STATIC METHODS ====================

    public static function getAggregatedStats($userId, $startDate = null, $endDate = null): array
    {
        $query = self::where('user_id', $userId);
        
        if ($startDate && $endDate) {
            $query->whereBetween('report_date', [$startDate, $endDate]);
        }

        return [
            'total_spend' => $query->sum('spend'),
            'total_impressions' => $query->sum('impressions'),
            'total_reach' => $query->sum('reach'),
            'total_clicks' => $query->sum('clicks'),
            'total_leads' => $query->sum('leads'),
            'avg_cpc' => $query->avg('cpc'),
            'avg_cpl' => $query->where('cpl', '>', 0)->avg('cpl'),
            'avg_ctr' => $query->avg('ctr'),
        ];
    }
}