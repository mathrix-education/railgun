<?php

declare(strict_types=1);

namespace Railgun\Support;

use DateTime;
use Faker\Generator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use function app;
use function collect;

class Timestamps
{
    private const CACHE_KEY  = 'timestamps.factory';
    private const CACHE_SIZE = 100;

    public DateTime $createdAt;
    public DateTime $updatedAt;
    public DateTime $deletedAt;

    private function __construct()
    {
        // Direct new() is forbidden
    }

    /**
     * @param mixed $start The minimal start date.
     * @param int   $bias  The probability that the updated_at is the same as the created_at.
     *
     * @return static
     */
    public static function factory($start = '-1 month', int $bias = 90): self
    {
        $generator = static function ($start, int $bias) {
            $timestamps = new self();
            /** @var Generator $faker The Faker generator instance */
            $faker = app(Generator::class);

            $timestamps->createdAt = $faker->dateTimeBetween($start);
            $timestamps->updatedAt = $faker->boolean($bias)
                ? $timestamps->createdAt
                : $faker->dateTimeBetween($timestamps->createdAt);
            $timestamps->deletedAt = $faker->boolean($bias)
                ? $timestamps->updatedAt
                : $faker->dateTimeBetween($timestamps->updatedAt);

            return $timestamps;
        };

        if ($start === '-1 month' && $bias === 90) {
            /** @var Collection $timestamps */
            $timestamps = Cache::remember(self::CACHE_KEY, 3600, static function () use (
                $start,
                $bias,
                $generator
            ) {
                $timestamps = [];
                for ($i = 0; $i < self::CACHE_SIZE; $i++) {
                    $timestamps[] = $generator($start, $bias);
                }

                return collect($timestamps);
            });

            return $timestamps->random();
        }

        return $generator($start, $bias);
    }
}
