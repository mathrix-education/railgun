<?php

use App\Models\Activity;
use Faker\Generator;

/**
 * ActivityFactory
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 *
 * @var Illuminate\Database\Eloquent\Factory $factory
 */
$factory->define(Activity::class, function (Generator $faker) {
    $name = mb_substr($faker->sentence(), 0, -1);

    return [
        "name" => $name,
        "slug" => str_slug($name),
        "seo_title" => $faker->boolean(70) ? $name : null,
        "seo_description" => $faker->boolean(70) ? $faker->text(10) : null,
        "type" => $faker->randomElement(Activity::TYPES),
        "visible" => $faker->boolean(95),
        "premium" => $faker->boolean(30),
        "reviewed" => $faker->boolean(),
    ];
});
