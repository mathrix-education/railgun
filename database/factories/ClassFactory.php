<?php

use App\Models\Class_;
use App\Models\Country;
use Faker\Generator;

/*
 * CLassFactory
 *
 * @author Mathrix Team
 * @var Illuminate\Database\Eloquent\Factory $factory
 */
$factory->define(Class_::class, function (Generator $faker) {
    $name = $faker->firstName;
    $slug = str_slug(mb_substr($name, 0, random_int(2, 3)));

    return [
        "name" => $name,
        "slug" => $slug,
        "country_id" => Country::inRandomOrder()
            ->firstOrFail()->id,
        "visible" => $faker->boolean(95),
    ];
});
