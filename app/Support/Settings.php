<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

/**
 * Database-backed settings that override config()/.env.
 *
 * Read on nearly every page render (the floating support buttons), so the whole
 * table is cached as one array and busted on write rather than queried per key.
 */
final class Settings
{
    private const CACHE_KEY = 'app.settings';

    /**
     * @return array<string, string|null>
     */
    public static function all(): array
    {
        return Cache::rememberForever(
            self::CACHE_KEY,
            static fn (): array => Setting::query()->pluck('value', 'key')->all(),
        );
    }

    /**
     * The stored value, or $default when unset or blank — so clearing a field
     * in the UI falls back to the .env value rather than rendering empty.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $value = self::all()[$key] ?? null;

        return ($value === null || $value === '') ? $default : $value;
    }

    /**
     * @param  array<string, string|null>  $values
     */
    public static function put(array $values): void
    {
        foreach ($values as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        self::flush();
    }

    public static function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
