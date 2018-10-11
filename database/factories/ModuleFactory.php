<?php

use App\Models\Module;
use Faker\Generator;

/***
 * ModuleFactory & ModuleRandomFactory
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 *
 * @var Illuminate\Database\Eloquent\Factory $factory
 */
$factory->define(Module::class, function (Generator $faker) {
    return [
        "premium" => $faker->boolean(70),
        "visible" => $faker->boolean(95),
    ];
});

$factory->defineAs(Module::class, "random", function (Generator $faker) use ($factory) {
    return $factory->rawOf(Module::class, $faker->randomElement([
        "course",
        "fill",
        "mcq",
        "mcq-table",
        "test",
        "flashcard",
    ]));
});
