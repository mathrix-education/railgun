<?php
use App\Models\Activity;
use Faker\Generator;

/**
 * ActivityFactory
 * @author Mathrix Team
 * @var Illuminate\Database\Eloquent\Factory $factory
 */
$factory->define(Activity::class, function (Generator $faker) {
    $name = substr($faker->sentence(), 0, -1);

    return [
        'name' => $name,
        'slug' => str_slug($name),
        'description' => $faker->sentence()
    ];
});