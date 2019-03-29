<?php

use App\Models\Discipline;
use Faker\Generator;
use Illuminate\Database\Eloquent\Factory;

/**
 * DisciplineFactory.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0
 *
 * @var Factory $factory
 */
$factory->define(Discipline::class, function (Generator $faker) {
    $name = mb_substr($faker->sentence(random_int(3, 5)), 0, -1);

    return [
        "name" => $name,
        "slug" => str_slug($name),
        "beta" => $faker->boolean()
    ];
});
