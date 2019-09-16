<?php

declare(strict_types=1);

use App\Models\Chapter;
use App\Models\Notion;
use Faker\Generator;
use Illuminate\Database\Eloquent\Factory;

/**
 * NotionFactory.
 *
 * @var Factory $factory
 */
$factory->define(Notion::class, static function (Generator $faker) {
    [$createdAt, $updatedAt] = timestamps();

    return [
        'name' => mb_substr($faker->sentence(), 0, -1),
        'type' => $faker->randomElement(Notion::TYPES),
        'visible' => $faker->boolean(95),
        'order' => $faker->numberBetween(0, 100),
        'chapter_id' => Chapter::random()->id,
        'created_at' => $createdAt,
        'updated_at' => $updatedAt,
    ];
});
