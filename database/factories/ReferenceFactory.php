<?php

use App\Models\Module;
use App\Models\Reference;
use Faker\Generator;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Str;

/**
 * ReferenceFactory.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0
 *
 * @var Factory $factory
 */
$factory->define(Reference::class, function (Generator $faker) {
    return [
        "key" => Str::slug(implode("-", $faker->words($faker->numberBetween(1, 3)))),
        "text" => $faker->text(),
        "module_id" => Module::random()->id
    ];
});
