<?php

use App\Models\Country;
use Faker\Generator;

/**
 * CountryFactory
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 *
 * @var Illuminate\Database\Eloquent\Factory $factory
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
