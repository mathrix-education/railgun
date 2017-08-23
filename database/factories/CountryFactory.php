<?php
use App\Models\Country;
use Faker\Generator;

/**
 * CountryFactory
 *
 * @author Mathrix Team
 * @var Illuminate\Database\Eloquent\Factory $factory
 */
$factory->define(Country::class, function (Generator $faker) {
    $name = $faker->firstName;
    $nameShort = substr($name, 0, -min([3, strlen($name) - 3]));

    return [
        'name' => $name,
        'slug' => str_slug($name),
        'name_short' => $nameShort,
        'slug_short' => str_slug($nameShort)
    ];
});