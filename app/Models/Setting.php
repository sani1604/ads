<?php

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
            'boolean' => filter_var($this->value, FILTER_VALIDATE_BOOLEAN),
            'number', 'integer' => (int) $this->value,
            'float' => (float) $this->value,
            'json', 'array' => $this->decodeJson($this->value),
            default => $this->value,
        };
    }

    /**
     * Safely decode JSON
     */
    protected function decodeJson($value)
    {
        if (is_array($value)) {
            return $value;
        }

        if (empty($value)) {
            return [];
        }

        $decoded = json_decode($value, true);
        
        return json_last_error() === JSON_ERROR_NONE ? $decoded : [];
    }

    // ==================== STATIC METHODS ====================

    /**
     * Get a setting value
     */
    public static function get(string $key, $default = null)
    {
        return Cache::remember("setting.{$key}", 3600, function () use ($key, $default) {
            $setting = self::where('key', $key)->first();
            return $setting ? $setting->typed_value : $default;
        });
    }

    /**
     * Set a setting value
     */
    public static function set(string $key, $value, ?string $type = null, string $group = 'general'): self
    {
        // Auto-detect type if not provided
        if ($type === null) {
            $type = self::detectType($value);
        }

        // Convert value to storable format
        $storableValue = self::prepareValueForStorage($value, $type);

        $setting = self::updateOrCreate(
            ['key' => $key],
            [
                'value' => $storableValue,
                'type' => $type,
                'group' => $group,
            ]
        );

        Cache::forget("setting.{$key}");

        return $setting;
    }

    /**
     * Detect the type of value
     */
    protected static function detectType($value): string
    {
        if (is_bool($value)) {
            return 'boolean';
        }

        if (is_array($value)) {
            return 'json';
        }

        if (is_int($value)) {
            return 'integer';
        }

        if (is_float($value)) {
            return 'float';
        }

        return 'text';
    }

    /**
     * Prepare value for database storage
     */
    protected static function prepareValueForStorage($value, string $type): string
    {
        // Handle arrays/objects - convert to JSON string
        if (is_array($value) || is_object($value)) {
            return json_encode($value);
        }

        // Handle booleans
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        // Handle null
        if (is_null($value)) {
            return '';
        }

        // Everything else - cast to string
        return (string) $value;
    }

    /**
     * Get all settings by group
     */
    public static function getByGroup(string $group): array
    {
        return self::where('group', $group)
            ->get()
            ->pluck('typed_value', 'key')
            ->toArray();
    }

    /**
     * Get all settings as array
     */
    public static function getAllSettings(): array
    {
        return self::all()
            ->pluck('typed_value', 'key')
            ->toArray();
    }

    /**
     * Delete a setting
     */
    public static function remove(string $key): bool
    {
        Cache::forget("setting.{$key}");
        return self::where('key', $key)->delete() > 0;
    }

    /**
     * Check if setting exists
     */
    public static function has(string $key): bool
    {
        return self::where('key', $key)->exists();
    }

    /**
     * Clear all settings cache
     */
    public static function clearCache(): void
    {
        $settings = self::all();
        foreach ($settings as $setting) {
            Cache::forget("setting.{$setting->key}");
        }
    }
}