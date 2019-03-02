<?php

use App\Models\Module;
use App\Models\Reference;
use Faker\Generator;
use Illuminate\Support\Str;

/**
 * ReferenceFactory
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 *
 * @var Illuminate\Database\Eloquent\Factory $factory
 */
$factory->define(Reference::class, function (Generator $faker) {
    return [
        "key" => Str::slug($faker->firstName . " " . $faker->lastName),
        "text" => $faker->text(),
        "module_id" => Module::random()->id
    ];
});
