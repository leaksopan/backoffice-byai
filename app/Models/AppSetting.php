<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class AppSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'updated_by',
    ];

    public static function getValue(string $key, mixed $default = null): mixed
    {
        if (! Schema::hasTable('app_settings')) {
            return $default;
        }

        return Cache::remember(self::cacheKey($key), now()->addDay(), function () use ($key, $default) {
            $setting = self::query()->where('key', $key)->first();

            if (! $setting) {
                return $default;
            }

            return $setting->resolveValue();
        });
    }

    public static function putValue(
        string $key,
        mixed $value,
        string $type = 'string',
        string $group = 'general',
        ?int $updatedBy = null
    ): self {
        $setting = self::query()->updateOrCreate(
            ['key' => $key],
            [
                'value' => self::normalizeValue($value, $type),
                'type' => $type,
                'group' => $group,
                'updated_by' => $updatedBy,
            ]
        );

        Cache::forget(self::cacheKey($key));

        return $setting;
    }

    private static function normalizeValue(mixed $value, string $type): ?string
    {
        return match ($type) {
            'json' => $value === null ? null : json_encode($value, JSON_UNESCAPED_SLASHES),
            'boolean' => $value ? '1' : '0',
            default => $value === null ? null : (string) $value,
        };
    }

    private function resolveValue(): mixed
    {
        if ($this->value === null) {
            return null;
        }

        return match ($this->type) {
            'int', 'integer' => (int) $this->value,
            'float', 'double' => (float) $this->value,
            'boolean' => $this->value === '1',
            'json' => json_decode($this->value, true),
            default => $this->value,
        };
    }

    private static function cacheKey(string $key): string
    {
        return "app_setting:{$key}";
    }
}
