<?php

declare(strict_types=1);

use App\Models\Chapter;
use App\Models\Notion;
use App\Support\Timestamps;
use Faker\Generator;
use Illuminate\Database\Eloquent\Factory;

/**
 * NotionFactory.
 *
 * @var Factory $factory
 */
$factory->define(Notion::class, static function (Generator $faker) {
    $name       = ucfirst($faker->word);
    $timestamps = Timestamps::factory();

    return [
        'name'       => $name,
        'type'       => $faker->randomElement(Notion::TYPES),
        'visible'    => $faker->boolean(95),
        'order'      => 99,
        'chapter_id' => Chapter::random()->id,
        'created_at' => $timestamps->createdAt,
        'updated_at' => $timestamps->updatedAt,
    ];
});
