<?php

use App\Models\Category;
use Faker\Generator;

/**
 * CategoryFactory
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 *
 * @var Illuminate\Database\Eloquent\Factory $factory
 */
$factory->define(Category::class, function (Generator $faker) {
    $name = mb_substr($faker->sentence($faker->numberBetween(3, 5)), 0, -1);

    return [
        "name" => $name,
        "slug" => str_slug($name),
    ];
});
