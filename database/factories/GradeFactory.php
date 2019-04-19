<?php

use App\Models\Country;
use App\Models\Grade;
use Faker\Generator;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Str;

/**
 * GradeFactory.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 *
 * @var Factory $factory
 */
$factory->define(Grade::class, function (Generator $faker) {
    $name = $faker->firstName;
    $slug = Str::slug(mb_substr($name, 0, random_int(2, 3)));

    return [
        "name" => $name,
        "slug" => $slug,
        "order" => $faker->numberBetween(0, 100),
        "parent_id" => null,
        "country_id" => Country::random()->id,
        "visible" => $faker->boolean(95),
    ];
});
