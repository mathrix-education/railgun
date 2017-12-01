<?php

use App\Models\Activity;
use App\Models\Section;
use App\Models\Training;
use Faker\Generator;

/**
 * SectionFactory
 *
 * @author Mathrix Team
 * @var Illuminate\Database\Eloquent\Factory $factory
 */
$factory->define(Training::class, function (Generator $faker) {
    $name = substr($faker->sentence(), 0, -1);

    return [
        "name" => $name,
        "slug" => str_slug($name),
        "description" => substr($faker->sentence(), -1)
    ];
});