<?php

declare(strict_types=1);

use Railgun\Models\Category;
use Faker\Generator;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Str;

/**
 * @var Factory $factory
 */
$factory->define(Category::class, static function (Generator $faker) {
    $name = ucfirst($faker->word);

    return [
        'name' => $name,
        'slug' => Str::slug($name),
    ];
});
