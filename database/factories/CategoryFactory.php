<?php

use App\Models\Category;
use Faker\Generator;

/**
 * CategoryFactory
 *
 * @author Mathrix Team
 * @var Illuminate\Database\Eloquent\Factory $factory
 */
$factory->define(Category::class, function (Generator $faker) {
    $name = substr($faker->sentence($faker->numberBetween(3, 5)), 0, -1);

    return [
        "name" => $name,
        "slug" => str_slug($name)
    ];
});
