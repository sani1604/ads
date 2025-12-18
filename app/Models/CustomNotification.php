<?php
// app/Models/CustomNotification.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'icon',
        'action_url',
        'data',
        'is_read',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'is_read' => 'boolean',
            'read_at' => 'datetime',
        ];
    }

    // ==================== RELATIONSHIPS ====================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ==================== SCOPES ====================

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // ==================== ACCESSORS ====================

    public function getIconClassAttribute(): string
    {
        return $this->icon ?? match($this->type) {
            'lead_received' => 'fas fa-user-plus text-success',
            'creative_approved' => 'fas fa-check-circle text-success',
            'creative_rejected' => 'fas fa-times-circle text-danger',
            'changes_requested' => 'fas fa-edit text-warning',
            'payment_success' => 'fas fa-credit-card text-success',
            'payment_failed' => 'fas fa-credit-card text-danger',
            'subscription_expiring' => 'fas fa-clock text-warning',
            'ticket_reply' => 'fas fa-comment text-info',
            default => 'fas fa-bell text-primary',
        };
    }

    // ==================== HELPER METHODS ====================

    public function markAsRead(): bool
    {
        if ($this->is_read) {
            return true;
        }

        return $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    public static function send(
        User $user,
        string $type,
        string $title,
        string $message,
        string $actionUrl = null,
        array $data = []
    ): self {
        return self::create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'action_url' => $actionUrl,
            'data' => $data,
        ]);
    }
}