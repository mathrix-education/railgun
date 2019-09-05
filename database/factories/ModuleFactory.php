<?php

use App\Models\Module;
use Faker\Generator;
use Illuminate\Database\Eloquent\Factory;

/**
 * ModuleFactory.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 *
 * @var Factory $factory
 */
$factory->define(Module::class, function (Generator $faker) use ($factory) {
    return $factory->rawOf(Module::class, $faker->randomElement([
        "course",
        "mcq",
        "test",
        "flashcard",
    ]));
});
