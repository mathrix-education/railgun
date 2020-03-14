<?php

declare(strict_types=1);

use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Str;
use Railgun\Models\Training;
use Railgun\Support\Timestamps;

/**
 * @var Factory $factory
 */
$factory->define(Training::class, static function (Faker $faker) {
    $name       = ucfirst($faker->word);
    $timestamps = Timestamps::factory();

    return [
        'name'       => $name,
        'slug'       => Str::ucfirst($name),
        'buyable'    => $faker->boolean(90),
        'price'      => $faker->numberBetween(4, 9) * 100,
        'order'      => 99,
        'visible'    => $faker->boolean(90),
        'created_at' => $timestamps->createdAt,
        'updated_at' => $timestamps->updatedAt,
    ];
});
