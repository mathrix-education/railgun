<?php

declare(strict_types=1);

use App\Models\Discipline;
use Faker\Generator;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Str;

/**
 * DisciplineFactory.
 *
 * @var Factory $factory
 */
$factory->define(Discipline::class, static function (Generator $faker) {
    $name = mb_substr($faker->sentence(random_int(3, 5)), 0, -1);

    return [
        'name' => $name,
        'slug' => Str::slug($name),
        'beta' => $faker->boolean(),
    ];
});
