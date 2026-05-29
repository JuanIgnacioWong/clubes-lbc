<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Setting extends Model
{
    use HasFactory;

    public const GLOBAL_CACHE_KEY = 'global_settings';
    public const PLATFORM_LOGO_SETTING_KEY = 'logo_institutional';

    protected $fillable = [
        'key',
        'value',
        'type',
    ];

    public static function getValue(string $key, ?string $default = null): ?string
    {
        return static::allCached()[$key] ?? $default;
    }

    public static function allCached(): array
    {
        return Cache::rememberForever(self::GLOBAL_CACHE_KEY, function (): array {
            return static::query()
                ->pluck('value', 'key')
                ->map(fn ($value): string => (string) $value)
                ->all();
        });
    }

    public static function forgetGlobalCache(): void
    {
        Cache::forget(self::GLOBAL_CACHE_KEY);
    }

    public static function globalValues(array $defaults = []): array
    {
        $values = static::allCached();

        foreach ($defaults as $key => $default) {
            $values[$key] = $values[$key] ?? (string) $default;
        }

        return $values;
    }

    public static function platformName(): string
    {
        return static::getValue('platform_name', 'Inscripción de clubes LBC Chile') ?? 'Inscripción de clubes LBC Chile';
    }

    public static function platformLogoPath(): string
    {
        $stored = (string) (static::getValue(self::PLATFORM_LOGO_SETTING_KEY, '') ?? '');
        $normalized = static::normalizePublicDiskPath($stored);

        return $normalized ?? '';
    }

    public static function platformLogoUrl(): ?string
    {
        $path = static::platformLogoPath();
        if ($path === '') {
            return null;
        }

        if (! Storage::disk('public')->exists($path)) {
            return null;
        }

        $url = Storage::disk('public')->url($path);

        // Keep same-origin behavior even when APP_URL is not aligned with the current host.
        return parse_url($url, PHP_URL_PATH) ?: $url;
    }

    public static function normalizePublicDiskPath(?string $storedPath): ?string
    {
        $value = trim((string) $storedPath);
        if ($value === '') {
            return null;
        }

        if (Str::startsWith($value, ['http://', 'https://'])) {
            $path = parse_url($value, PHP_URL_PATH);
            $value = is_string($path) ? $path : '';
        }

        $value = ltrim($value, '/');

        if (Str::startsWith($value, 'storage/app/public/')) {
            $value = Str::after($value, 'storage/app/public/');
        }

        if (Str::startsWith($value, 'public/storage/')) {
            $value = Str::after($value, 'public/storage/');
        }

        if (Str::startsWith($value, 'storage/')) {
            $value = Str::after($value, 'storage/');
        }

        return $value !== '' ? $value : null;
    }
}
