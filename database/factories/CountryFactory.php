<?php

declare(strict_types=1);

use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Str;
use Railgun\Models\Country;

/**
 * @var Factory $factory
 */
$factory->define(Country::class, static function (Faker $faker) {
    $name = ucfirst($faker->country);

    return [
        'name'    => $name,
        'slug'    => Str::slug($name),
        'visible' => $faker->boolean(95),
    ];
});
