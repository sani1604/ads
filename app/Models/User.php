<?php
// app/Models/User.php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'company_name',
        'company_website',
        'industry_id',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'gst_number',
        'avatar',
        'wallet_balance',
        'is_active',
        'is_onboarded',
        'onboarding_data',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'wallet_balance' => 'decimal:2',
            'is_active' => 'boolean',
            'is_onboarded' => 'boolean',
            'onboarding_data' => 'array',
        ];
    }

    // ==================== RELATIONSHIPS ====================

    public function industry(): BelongsTo
    {
        return $this->belongsTo(Industry::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function activeSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class)
            ->where('status', 'active')
            ->latest();
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function walletTransactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class);
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

    public function notifications(): HasMany
    {
        return $this->hasMany(CustomNotification::class);
    }

    public function supportTickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    // ==================== SCOPES ====================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeClients($query)
    {
        return $query->where('role', 'client');
    }

    public function scopeAdmins($query)
    {
        return $query->whereIn('role', ['admin', 'manager']);
    }

    public function scopeOnboarded($query)
    {
        return $query->where('is_onboarded', true);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%")
              ->orWhere('company_name', 'like', "%{$search}%");
        });
    }

    // ==================== ACCESSORS ====================

    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            return asset('storage/' . $this->avatar);
        }
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=6366f1&color=fff';
    }

    public function getFormattedWalletBalanceAttribute(): string
    {
        return '₹' . number_format($this->wallet_balance, 2);
    }

    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address,
            $this->city,
            $this->state,
            $this->postal_code,
            $this->country,
        ]);
        return implode(', ', $parts);
    }

    // ==================== HELPER METHODS ====================

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isManager(): bool
    {
        return $this->role === 'manager';
    }

    public function isClient(): bool
    {
        return $this->role === 'client';
    }

    public function isStaff(): bool
    {
        return in_array($this->role, ['admin', 'manager']);
    }

    public function hasActiveSubscription(): bool
    {
        return $this->subscriptions()
            ->where('status', 'active')
            ->where('end_date', '>=', now())
            ->exists();
    }

    public function canAccessDashboard(): bool
    {
        return $this->is_active && $this->is_onboarded;
    }

    public function creditWallet(float $amount, string $description = null, $referenceType = null, $referenceId = null): WalletTransaction
    {
        $balanceBefore = $this->wallet_balance;
        $this->increment('wallet_balance', $amount);
        
        return $this->walletTransactions()->create([
            'type' => 'credit',
            'amount' => $amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $this->wallet_balance,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'description' => $description,
        ]);
    }

    public function debitWallet(float $amount, string $description = null, $referenceType = null, $referenceId = null): WalletTransaction
    {
        if ($this->wallet_balance < $amount) {
            throw new \Exception('Insufficient wallet balance');
        }

        $balanceBefore = $this->wallet_balance;
        $this->decrement('wallet_balance', $amount);
        
        return $this->walletTransactions()->create([
            'type' => 'debit',
            'amount' => $amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $this->wallet_balance,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'description' => $description,
        ]);
    }

    public function getUnreadNotificationsCount(): int
    {
        return $this->notifications()->where('is_read', false)->count();
    }

    public function getLeadsThisMonth(): int
    {
        return $this->leads()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
    }

    public function getTotalAdSpendThisMonth(): float
    {
        return $this->campaignReports()
            ->whereMonth('report_date', now()->month)
            ->whereYear('report_date', now()->year)
            ->sum('spend');
    }
}