<?php

declare(strict_types=1);

use Faker\Generator;
use Illuminate\Database\Eloquent\Factory;
use Railgun\Models\Chapter;
use Railgun\Models\Notion;
use Railgun\Support\Timestamps;

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
        'visible'    => $faker->boolean(95),
        'order'      => 99,
        'chapter_id' => Chapter::random()->id,
        'created_at' => $timestamps->createdAt,
        'updated_at' => $timestamps->updatedAt,
    ];
});
