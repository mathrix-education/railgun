<?php

declare(strict_types=1);

use Faker\Generator;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Str;
use Railgun\Models\Pack;
use Railgun\Support\Timestamps;

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
