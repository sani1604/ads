<?php
// app/Models/Creative.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Creative extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'subscription_id',
        'service_category_id',
        'title',
        'description',
        'type',
        'platform',
        'status',
        'version',
        'parent_id',
        'dimensions',
        'ad_copy',
        'cta_text',
        'landing_url',
        'scheduled_date',
        'approved_at',
        'approved_by',
    ];

    protected function casts(): array
    {
        return [
            'dimensions' => 'array',
            'scheduled_date' => 'date',
            'approved_at' => 'datetime',
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

    public function serviceCategory(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Creative::class, 'parent_id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(Creative::class, 'parent_id');
    }

    public function files(): HasMany
    {
        return $this->hasMany(CreativeFile::class)->orderBy('sort_order');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(CreativeComment::class)->whereNull('parent_id');
    }

    public function allComments(): HasMany
    {
        return $this->hasMany(CreativeComment::class);
    }

    // ==================== SCOPES ====================

    public function scopePendingApproval($query)
    {
        return $query->where('status', 'pending_approval');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeChangesRequested($query)
    {
        return $query->where('status', 'changes_requested');
    }

    public function scopeForPlatform($query, $platform)
    {
        return $query->where(function ($q) use ($platform) {
            $q->where('platform', $platform)
              ->orWhere('platform', 'all');
        });
    }

    public function scopeLatestVersions($query)
    {
        return $query->whereNull('parent_id');
    }

    // ==================== ACCESSORS ====================

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'draft' => '<span class="badge bg-secondary">Draft</span>',
            'pending_approval' => '<span class="badge bg-warning">Pending Approval</span>',
            'changes_requested' => '<span class="badge bg-info">Changes Requested</span>',
            'approved' => '<span class="badge bg-success">Approved</span>',
            'rejected' => '<span class="badge bg-danger">Rejected</span>',
            'published' => '<span class="badge bg-primary">Published</span>',
            default => '<span class="badge bg-secondary">Unknown</span>',
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'image' => 'Image',
            'video' => 'Video',
            'carousel' => 'Carousel',
            'story' => 'Story',
            'reel' => 'Reel',
            'document' => 'Document',
            default => 'Other',
        };
    }

    public function getPlatformIconAttribute(): string
    {
        return match($this->platform) {
            'facebook' => 'fab fa-facebook',
            'instagram' => 'fab fa-instagram',
            'google' => 'fab fa-google',
            'linkedin' => 'fab fa-linkedin',
            'twitter' => 'fab fa-twitter',
            'youtube' => 'fab fa-youtube',
            'all' => 'fas fa-globe',
            default => 'fas fa-question',
        };
    }

    public function getPrimaryFileAttribute()
    {
        return $this->files()->first();
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        $file = $this->primary_file;
        if ($file) {
            return asset('storage/' . $file->file_path);
        }
        return null;
    }

    public function getUnresolvedCommentsCountAttribute(): int
    {
        return $this->allComments()->where('is_resolved', false)->count();
    }

    // ==================== HELPER METHODS ====================

    public function isPending(): bool
    {
        return $this->status === 'pending_approval';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function needsChanges(): bool
    {
        return $this->status === 'changes_requested';
    }

    public function approve(User $approver): bool
    {
        return $this->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => $approver->id,
        ]);
    }

    public function requestChanges(): bool
    {
        return $this->update([
            'status' => 'changes_requested',
        ]);
    }

    public function reject(): bool
    {
        return $this->update([
            'status' => 'rejected',
        ]);
    }

    public function submitForApproval(): bool
    {
        return $this->update([
            'status' => 'pending_approval',
        ]);
    }

    public function createNewVersion(): Creative
    {
        $newVersion = $this->replicate();
        $newVersion->parent_id = $this->parent_id ?? $this->id;
        $newVersion->version = $this->getLatestVersion() + 1;
        $newVersion->status = 'draft';
        $newVersion->approved_at = null;
        $newVersion->approved_by = null;
        $newVersion->save();

        return $newVersion;
    }

    public function getLatestVersion(): int
    {
        $parentId = $this->parent_id ?? $this->id;
        
        return Creative::where(function ($q) use ($parentId) {
            $q->where('id', $parentId)
              ->orWhere('parent_id', $parentId);
        })->max('version') ?? 1;
    }
}