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
    $nameShort = $faker->firstName;
    $name = ucfirst(strtolower($nameShort . $faker->firstName));

    return [
        "name" => $name,
        "slug" => str_slug($name),
        "name_short" => $nameShort,
        "slug_short" => str_slug($nameShort),
        "visible" => $faker->boolean(95)
    ];
});