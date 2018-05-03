<?php

use App\Models\Activity;
use Faker\Generator;

/**
 * ActivityFactory
 *
 * @author Mathrix Team
 * @var Illuminate\Database\Eloquent\Factory $factory
 */
$factory->define(Activity::class, function (Generator $faker) {
    $name = substr($faker->sentence(), 0, -1);

    return [
        "name" => $name,
        "name_seo" => $faker->boolean(70) ? $name : null,
        "slug" => str_slug($name),
        "type" => $faker->randomElement(Activity::TYPES),
        "visible" => $faker->boolean(95)
    ];
});
