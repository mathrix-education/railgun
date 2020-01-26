<?php

declare(strict_types=1);

use Faker\Generator;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Str;
use Railgun\Models\Discipline;

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
