<?php
// app/Models/CreativeFile.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class CreativeFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'creative_id',
        'file_name',
        'original_name',
        'file_path',
        'file_type',
        'mime_type',
        'file_size',
        'sort_order',
    ];

    // ==================== RELATIONSHIPS ====================

    public function creative(): BelongsTo
    {
        return $this->belongsTo(Creative::class);
    }

    // ==================== ACCESSORS ====================

    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->file_path);
    }

    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function getIsImageAttribute(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    public function getIsVideoAttribute(): bool
    {
        return str_starts_with($this->mime_type, 'video/');
    }

    public function getExtensionAttribute(): string
    {
        return pathinfo($this->original_name, PATHINFO_EXTENSION);
    }

    // ==================== HELPER METHODS ====================

    public function delete(): bool
    {
        // Delete file from storage
        if (Storage::disk('public')->exists($this->file_path)) {
            Storage::disk('public')->delete($this->file_path);
        }

        return parent::delete();
    }
}