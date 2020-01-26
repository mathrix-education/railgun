<?php

declare(strict_types=1);

use Faker\Generator;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Str;
use Railgun\Models\Country;
use Railgun\Models\Grade;

/**
 * GradeFactory.
 *
 * @var Factory $factory
 */
$factory->define(Grade::class, static function (Generator $faker) {
    $name = ucfirst($faker->word);

    return [
        'name'       => $name,
        'slug'       => Str::slug($name),
        'order'      => $faker->numberBetween(0, 100),
        'parent_id'  => null,
        'country_id' => Country::random()->id,
        'visible'    => $faker->boolean(95),
    ];
});
