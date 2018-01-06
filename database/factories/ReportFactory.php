<?php

use App\Models\Module;
use App\Models\Quote;
use App\Models\Report;
use Faker\Generator;

/**
 * QuoteFactory
 *
 * @author Mathrix Team
 * @var Illuminate\Database\Eloquent\Factory $factory
 */
$factory->define(Report::class, function (Generator $faker) {
    return [
        "content" => $faker->sentence(10),
        "status" => $faker->randomElement(Report::STATUSES),
        "module_id" => Module::random()->id
    ];
});
