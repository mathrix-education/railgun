<?php

use App\Models\Module;
use App\Models\Product;
use Faker\Generator;

/**
 * PostFactory
 *
 * @author Mathrix Team
 * @var Illuminate\Database\Eloquent\Factory $factory
 */
$factory->define(Product::class, function (Generator $faker) {
    $name = substr($faker->sentence($faker->numberBetween(3, 5)), 0, -1);
    $created = $faker->dateTimeBetween("-30 days", "now");

    return [
        "name" => $name,
        "slug" => str_slug($name),
        "image" => null,
        "type" => null,
        "description" => $faker->sentence,
        "price" => $faker->boolean ? random_int(5, 40) : null,
        "visible" => $faker->boolean,
        "module_id" => $faker->boolean ? Module::random()->id : null,
        "created_at" => $created,
        "updated_at" => $faker->boolean(10) ? $faker->dateTimeBetween($created, "now") : $created,
    ];
});
