<?php

declare(strict_types=1);

use Railgun\Models\Discipline;
use Faker\Generator;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Str;

/**
 * @var Factory $factory
 */
$factory->define(Discipline::class, static function (Generator $faker) {
    $name = ucfirst($faker->word);

    return [
        'name' => $name,
        'slug' => Str::slug($name),
        'beta' => $faker->boolean(),
    ];
});
