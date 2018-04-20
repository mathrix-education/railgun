<?php

use App\Models\Quote;
use Faker\Generator;

/**
 * QuoteFactory
 *
 * @author Mathrix Team
 * @var Illuminate\Database\Eloquent\Factory $factory
 */
$factory->define(Quote::class, function (Generator $faker) {
    return [
        "content" => $faker->sentence(10),
        "author" => $faker->firstName . " " . $faker->lastName
    ];
});
