<?php

declare(strict_types=1);

use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Str;
use Railgun\Models\Grade;

/**
 * GradeFactory.
 *
 * @var Factory $factory
 */
$factory->define(Grade::class, static function (Faker $faker) {
    $name = ucfirst($faker->word);

    return [
        'name'       => $name,
        'slug'       => Str::slug($name),
        'order'      => $faker->numberBetween(0, 100),
        'parent_id'  => null,
        'visible'    => $faker->boolean(95),
    ];
});
