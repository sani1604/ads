<?php
// app/Models/Setting.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'group',
        'key',
        'value',
        'type',
        'description',
    ];

    // ==================== SCOPES ====================

    public function scopeByGroup($query, $group)
    {
        return $query->where('group', $group);
    }

    // ==================== ACCESSORS ====================

    public function getTypedValueAttribute()
    {
        return match($this->type) {
            'boolean' => (bool) $this->value,
            'number' => (float) $this->value,
            'json' => json_decode($this->value, true),
            default => $this->value,
        };
    }

    // ==================== STATIC METHODS ====================

    public static function get(string $key, $default = null)
    {
        return Cache::rememberForever("setting.{$key}", function () use ($key, $default) {
            $setting = self::where('key', $key)->first();
            return $setting ? $setting->typed_value : $default;
        });
    }

    public static function set(string $key, $value, string $type = 'text', string $group = 'general'): self
    {
        if ($type === 'json' && is_array($value)) {
            $value = json_encode($value);
        }

        $setting = self::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'type' => $type,
                'group' => $group,
            ]
        );

        Cache::forget("setting.{$key}");

        return $setting;
    }

    public static function getByGroup(string $group): array
    {
        return self::where('group', $group)
            ->get()
            ->pluck('typed_value', 'key')
            ->toArray();
    }

    public static function clearCache(): void
    {
        $settings = self::all();
        foreach ($settings as $setting) {
            Cache::forget("setting.{$setting->key}");
        }
    }
}