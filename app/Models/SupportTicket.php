<?php
// app/Models/SupportTicket.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupportTicket extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'ticket_number',
        'subject',
        'category',
        'priority',
        'status',
        'assigned_to',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'resolved_at' => 'datetime',
        ];
    }

    // ==================== RELATIONSHIPS ====================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(SupportTicketMessage::class, 'ticket_id');
    }

    // ==================== SCOPES ====================

    public function scopeOpen($query)
    {
        return $query->whereIn('status', ['open', 'in_progress', 'waiting_reply']);
    }

    public function scopeClosed($query)
    {
        return $query->whereIn('status', ['resolved', 'closed']);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    // ==================== ACCESSORS ====================

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'open' => '<span class="badge bg-primary">Open</span>',
            'in_progress' => '<span class="badge bg-info">In Progress</span>',
            'waiting_reply' => '<span class="badge bg-warning">Waiting Reply</span>',
            'resolved' => '<span class="badge bg-success">Resolved</span>',
            'closed' => '<span class="badge bg-secondary">Closed</span>',
            default => '<span class="badge bg-secondary">Unknown</span>',
        };
    }

    public function getPriorityBadgeAttribute(): string
    {
        return match($this->priority) {
            'urgent' => '<span class="badge bg-danger">Urgent</span>',
            'high' => '<span class="badge bg-warning">High</span>',
            'medium' => '<span class="badge bg-info">Medium</span>',
            'low' => '<span class="badge bg-secondary">Low</span>',
            default => '<span class="badge bg-secondary">Unknown</span>',
        };
    }

    public function getCategoryLabelAttribute(): string
    {
        return match($this->category) {
            'billing' => 'Billing',
            'technical' => 'Technical',
            'creative' => 'Creative',
            'leads' => 'Leads',
            'general' => 'General',
            default => 'Other',
        };
    }

    // ==================== HELPER METHODS ====================

    public static function generateTicketNumber(): string
    {
        do {
            $number = 'TKT-' . now()->format('Ymd') . '-' . strtoupper(substr(uniqid(), -4));
        } while (self::where('ticket_number', $number)->exists());

        return $number;
    }

    public function resolve(): bool
    {
        return $this->update([
            'status' => 'resolved',
            'resolved_at' => now(),
        ]);
    }

    public function close(): bool
    {
        return $this->update(['status' => 'closed']);
    }

    public function reopen(): bool
    {
        return $this->update([
            'status' => 'open',
            'resolved_at' => null,
        ]);
    }

    public function getLastMessage()
    {
        return $this->messages()->latest()->first();
    }
}