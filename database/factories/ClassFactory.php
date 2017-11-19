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
    $name = ucfirst(strtolower($nameShort . $faker->firstName));

    return [
        "name" => $name,
        "slug" => str_slug($name),
        "name_short" => $nameShort,
        "slug_short" => str_slug($nameShort),
        "country_id" => Country::inRandomOrder()->firstOrFail()->id,
        "visible" => $faker->boolean(95)
    ];
});