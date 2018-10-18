<?php

use App\Models\Notion;
use Faker\Generator;

/**
 * NotionFactory
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 *
 * @var Illuminate\Database\Eloquent\Factory $factory
 */
$factory->define(Notion::class, function (Generator $faker) {
    $name = mb_substr($faker->sentence($faker->numberBetween(3, 5)), 0, -1);

    return [
        "name" => $name,
        "slug" => str_slug($name),
        "type" => $faker->randomElement(Notion::TYPES),
        "visible" => $faker->boolean(95),
    ];
});
