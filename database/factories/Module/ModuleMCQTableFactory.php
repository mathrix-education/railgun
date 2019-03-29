<?php

use App\Models\Module;
use Faker\Generator;
use Illuminate\Database\Eloquent\Factory;

/**
 * ModuleTableFactory.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0
 *
 * @var Factory $factory
 */
$factory->defineAs(Module::class, "mcq-table", function (Generator $faker) use ($factory) {
    $data = $factory->rawOf(Module::class, "mcq");

    $data["choices"] = $data["choices"][0];

    foreach ($data["corrections"] as $k => $v) {
        $data["corrections"][$k] = $v[0];
    }

    foreach ($data["solutions"] as $k => $v) {
        $data["solutions"][$k] = $faker->randomElements(
            array_keys($data["choices"]),
            $faker->numberBetween(1, count($data["choices"]))
        );
    }

    $data["type"] = "mcq-table";

    return $data;
});
