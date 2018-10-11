<?php

use App\Models\Discipline;
use Faker\Generator;

/**
 * DisciplineFactory
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 *
 * @var Illuminate\Database\Eloquent\Factory $factory
 */
$factory->define(Discipline::class, function (Generator $faker) {
    $name = mb_substr($faker->sentence(random_int(3, 5)), 0, -1);

    return [
        "name" => $name,
        "slug" => str_slug($name),
        "beta" => $faker->boolean()
    ];
});
