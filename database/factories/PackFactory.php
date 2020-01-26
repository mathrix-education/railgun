<?php

declare(strict_types=1);

use App\Models\Chapter;
use App\Models\Pack;
use App\Support\Timestamps;
use Faker\Generator;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Str;

/**
 * NotionFactory.
 *
 * @var Factory $factory
 */
$factory->define(Pack::class, static function (Generator $faker) {
    $name       = $faker->word;
    $timestamps = Timestamps::factory();

    return [
        'name'       => $name,
        'slug'       => Str::slug($name),
        'buyable'    => $faker->boolean(90),
        'price'      => $faker->numberBetween(5, 7) * 1000,
        'order'      => 99,
        'visible'    => $faker->boolean(90),
        'created_at' => $timestamps->createdAt,
        'updated_at' => $timestamps->updatedAt,
    ];
});
