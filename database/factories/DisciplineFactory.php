<?php

declare(strict_types=1);

use App\Models\Discipline;
use Faker\Generator;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Str;

/**
 * @var Factory $factory
 */
$factory->define(Discipline::class, static function (Generator $faker) {
    $name = $faker->word;

    return [
        'name' => $name,
        'slug' => Str::slug($name),
        'beta' => $faker->boolean(),
    ];
});
