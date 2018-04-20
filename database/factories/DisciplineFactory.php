<?php

use App\Models\Discipline;
use Faker\Generator;

/**
 * DisciplineFactory
 *
 * @author Mathrix Team
 * @var Illuminate\Database\Eloquent\Factory $factory
 */
$factory->define(Discipline::class, function (Generator $faker) {
    $name = substr($faker->sentence(random_int(3, 5)), 0, -1);

    return [
        "name" => $name,
        "slug" => str_slug($name),
        "beta" => $faker->boolean(),
        "visible" => $faker->boolean(95)
    ];
});
