<?php

declare(strict_types=1);

use App\Models\Country;
use Faker\Generator;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Str;

/**
 * @var Factory $factory
 */
$factory->define(Country::class, static function (Generator $faker) {
    $name = ucfirst($faker->country);

    return [
        'name' => $name,
        'slug' => Str::slug($name),
        'visible' => $faker->boolean(95),
    ];
});
