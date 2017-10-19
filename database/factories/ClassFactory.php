<?php
use App\Models\Class_;
use App\Models\Country;
use Faker\Generator;

/**
 * CLassFactory
 *
 * @author Mathrix Team
 * @var Illuminate\Database\Eloquent\Factory $factory
 */
$factory->define(Class_::class, function (Generator $faker) {
    $nameShort = $faker->firstName;
    $name = $nameShort . $faker->firstName;

    return [
        'name' => $name,
        'slug' => str_slug($name),
        'name_short' => $nameShort,
        'slug_short' => str_slug($nameShort),
        'country_id' => $faker->randomElement(Country::all()->pluck('id')->toArray()),
        'visible' => $faker->boolean(95)
    ];
});