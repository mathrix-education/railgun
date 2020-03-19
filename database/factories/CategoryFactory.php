<?php

declare(strict_types=1);

use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Str;
use Railgun\Models\Category;

/**
 * @var Factory $factory
 */
$factory->define(Category::class, static function (Faker $faker) {
    $name = ucfirst($faker->word);

    return [
        'name' => $name,
        'slug' => Str::slug($name),
    ];
});
