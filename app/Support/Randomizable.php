<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use function class_basename;
use function strtolower;

/**
 * @mixin Model
 */
trait Randomizable
{
    private static int  $CACHE_SIZE           = 1000;
    private static bool $shouldRandomizeCache = false;

    /**
     * @return static
     *
     * @noinspection PhpIncompatibleReturnTypeInspection
     * @noinspection ReturnTypeCanBeDeclaredInspection
     */
    public static function random()
    {
        if (static::$shouldRandomizeCache) {
            $key = strtolower(class_basename(static::class)) . '.random';

            /** @var Collection $cache */
            $cache = Cache::remember($key, 1, static function () {
                return static::query()
                    ->inRandomOrder()
                    ->limit(static::$CACHE_SIZE)
                    ->get();
            });

            return $cache->random();
        }

        return static::query()->inRandomOrder()->firstOrFail();
    }

    /**
     * Enable randomization for faster testing and seeding.
     *
     * @param bool $value
     */
    public static function enableRandomizationCache($value = true): void
    {
        static::$shouldRandomizeCache = $value;
    }

    /**
     * Disable the randomization cache.
     */
    public static function disableRandomizationCache(): void
    {
        static::enableRandomizationCache(false);
    }
}
