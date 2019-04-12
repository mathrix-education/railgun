<?php

use App\Models\Discipline;
use Faker\Generator;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Str;

/**
 * DisciplineFactory.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 *
 * @var Factory $factory
 */
$factory->define(Discipline::class, function (Generator $faker) {
    $name = mb_substr($faker->sentence(random_int(3, 5)), 0, -1);

    return [
        "name" => $name,
        "slug" => Str::slug($name),
        "beta" => $faker->boolean()
    ];
});
