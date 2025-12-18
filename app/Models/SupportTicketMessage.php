<?php
// app/Models/SupportTicketMessage.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportTicketMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'user_id',
        'message',
        'attachments',
        'is_internal_note',
    ];

    protected function casts(): array
    {
        return [
            'attachments' => 'array',
            'is_internal_note' => 'boolean',
        ];
    }

    // ==================== RELATIONSHIPS ====================

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(SupportTicket::class, 'ticket_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ==================== SCOPES ====================

    public function scopePublic($query)
    {
        return $query->where('is_internal_note', false);
    }

    public function scopeInternal($query)
    {
        return $query->where('is_internal_note', true);
    }

    // ==================== ACCESSORS ====================

    public function getHasAttachmentsAttribute(): bool
    {
        return !empty($this->attachments);
    }

    public function getAttachmentCountAttribute(): int
    {
        return $this->attachments ? count($this->attachments) : 0;
    }
}