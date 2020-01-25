<?php

declare(strict_types=1);

namespace App\Support;

use DateTime;
use Faker\Generator;
use function app;

class Timestamps
{
    public DateTime $createdAt;
    public DateTime $updatedAt;
    public DateTime $deletedAt;

    private function __construct()
    {
    }

    public static function factory($start = '-1 month', int $bias = 90): self
    {
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
    }
}
