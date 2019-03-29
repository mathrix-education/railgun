<?php

use App\Models\Country;
use Faker\Generator;
use Illuminate\Database\Eloquent\Factory;

/**
 * CountryFactory.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0
 *
 * @var Factory $factory
 */
$factory->define(Country::class, function (Generator $faker) {
    $name = $faker->firstName;
    $slug = str_slug(mb_substr($name, 0, random_int(2, 3)));

    return [
        "name" => $name,
        "slug" => str_slug($slug),
        "visible" => $faker->boolean(95),
    ];
});
