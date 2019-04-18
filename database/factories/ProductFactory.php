<?php

use App\Models\Module;
use App\Models\Product;
use Faker\Generator;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Str;

/**
 * ProductFactory.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 *
 * @var Factory $factory
 */
$factory->define(Product::class, function (Generator $faker) {
    $name = mb_substr($faker->sentence($faker->numberBetween(1, 3)), 0, -1);
    [$createdAt, $updatedAt] = timestamps();

    return [
        "name" => $name,
        "slug" => Str::slug($name),
        "image" => "img/products/{$faker->uuid}.jpg",
        "type" => $faker->randomElement(["pack", "training", "section", "lesson"]),
        "description" => $faker->sentence,
        "price" => $faker->boolean ? random_int(5, 40) * 10 : null,
        "visible" => $faker->boolean,
        "order" => $faker->numberBetween(0, 100),
        "module_id" => $faker->boolean(30) ? Module::random()->id : null,
        "created_at" => $createdAt,
        "updated_at" => $updatedAt,
    ];
});
