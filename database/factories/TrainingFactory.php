<?php

declare(strict_types=1);

use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Str;
use Railgun\Models\Training;
use Railgun\Support\Silicate;
use Railgun\Support\Timestamps;

/**
 * @var Factory $factory
 */
$factory->define(Training::class, static function (Faker $faker) {
    $name       = Str::ucfirst($faker->words(3, true));
    $timestamps = Timestamps::factory();

    return [
        'name'       => sprintf('%s (%s)', $name, $faker->randomNumber(6)),
        'slug'       => Str::slug($name),
        'duration'   => $faker->numberBetween(1, 4) * 10,
        'content'    => Silicate::wall(),
        'available'  => $faker->boolean(90),
        'price'      => $faker->numberBetween(4, 9) * 1000,
        'order'      => 99,
        'visible'    => $faker->boolean(90),
        'created_at' => $timestamps->createdAt,
        'updated_at' => $timestamps->updatedAt,
    ];
});
