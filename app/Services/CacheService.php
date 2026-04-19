<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class CacheService
{
    public const TTL_ROLE = 3600;
    public const TTL_CATEGORY_MENU = 1800;
    public const TTL_USER_PERMISSIONS = 300;
    public const TTL_FEATURED_ITEMS = 900;
    public const TTL_REPORT = 600;

    public const KEY_PREFIX = 'cafe:';

    public static function role(string $slug): string
    {
        return self::KEY_PREFIX . "role:{$slug}";
    }

    public static function categoryWithMenu(int $categoryId): string
    {
        return self::KEY_PREFIX . "category_menu:{$categoryId}";
    }

    public static function userPermissions(int $userId): string
    {
        return "user:{$userId}:permissions";
    }

    public static function allUserPermissions(int $userId): string
    {
        return "user:{$userId}:all_permissions";
    }

    public static function featuredItems(): string
    {
        return self::KEY_PREFIX . 'featured_items';
    }

    public static function report(string $type, string $startDate, string $endDate, ?string $variant = null): string
    {
        $key = self::KEY_PREFIX . "report:{$type}:{$startDate}:{$endDate}";

        if ($variant !== null && $variant !== '') {
            $key .= ":{$variant}";
        }

        return $key;
    }

    public static function reportPending(string $type, string $startDate, string $endDate, ?string $variant = null): string
    {
        return self::report($type, $startDate, $endDate, $variant) . ':pending';
    }

    public static function invalidationKey(string $model, int $id): string
    {
        return self::KEY_PREFIX . "invalidate:{$model}:{$id}";
    }

    public static function remember(string $key, int $ttl, callable $callback)
    {
        if (! self::isAvailable()) {
            return $callback();
        }

        return Cache::remember($key, $ttl, $callback);
    }

    public static function forget(string $key): void
    {
        if (! self::isAvailable()) {
            return;
        }

        Cache::forget($key);
    }

    public static function isAvailable(): bool
    {
        try {
            return in_array(config('cache.default'), ['redis', 'memcached', 'database'], true)
                && Schema::hasTable('cache');
        } catch (\Exception $e) {
            return false;
        }
    }
}
