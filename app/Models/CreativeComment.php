<?php
// app/Models/CreativeComment.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CreativeComment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'creative_id',
        'user_id',
        'parent_id',
        'comment',
        'position',
        'is_resolved',
    ];

    protected function casts(): array
    {
        return [
            'position' => 'array',
            'is_resolved' => 'boolean',
        ];
    }

    // ==================== RELATIONSHIPS ====================

    public function creative(): BelongsTo
    {
        return $this->belongsTo(Creative::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(CreativeComment::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(CreativeComment::class, 'parent_id');
    }

    // ==================== SCOPES ====================

    public function scopeUnresolved($query)
    {
        return $query->where('is_resolved', false);
    }

    public function scopeResolved($query)
    {
        return $query->where('is_resolved', true);
    }

    public function scopeParentComments($query)
    {
        return $query->whereNull('parent_id');
    }

    // ==================== ACCESSORS ====================

    public function getHasPositionAttribute(): bool
    {
        return !empty($this->position) && isset($this->position['x']) && isset($this->position['y']);
    }

    // ==================== HELPER METHODS ====================

    public function resolve(): bool
    {
        return $this->update(['is_resolved' => true]);
    }

    public function unresolve(): bool
    {
        return $this->update(['is_resolved' => false]);
    }

    public function isReply(): bool
    {
        return !is_null($this->parent_id);
    }
}