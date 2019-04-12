<?php

use App\Models\Module;
use Faker\Generator;
use Illuminate\Database\Eloquent\Factory;

/**
 * ModuleTestFactory.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0
 *
 * @var Factory $factory
 */
$factory->defineAs(Module::class, "test", function (Generator $faker) use ($factory) {
    $data = $factory->rawOf(Module::class, "mcq");

    $data["choices"] = null;
    $data["solutions"] = null;

    $data["type"] = "test";
    $data["options"] = [
        "duration" => ($faker->randomElement([10, 15, 20, 25, 30])) * 60,
    ];

    return $data;
});
