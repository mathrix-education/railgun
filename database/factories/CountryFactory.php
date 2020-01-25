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
    $name = $faker->country;
    $slug = Str::slug(mb_substr($name, 0, random_int(2, 3)));

    return [
        'name' => $name,
        'slug' => Str::slug($slug),
        'visible' => $faker->boolean(95),
    ];
});
