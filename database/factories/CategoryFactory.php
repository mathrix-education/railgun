<?php

declare(strict_types=1);

use App\Models\Category;
use Faker\Generator;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Str;

/**
 * CategoryFactory.
 *
 * @var Factory $factory
 */
$factory->define(Category::class, static function (Generator $faker) {
    $name = mb_substr($faker->sentence($faker->numberBetween(3, 5)), 0, -1);

    return [
        'name' => $name,
        'slug' => Str::slug($name),
        'image' => "img/categories/{$faker->uuid}.jpg",
    ];
});
