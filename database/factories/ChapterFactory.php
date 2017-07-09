<?php
$factory->define(App\Models\Chapter::class, function(Faker\Generator $faker) {
    $name = substr($faker->sentence($faker->numberBetween(1, 3)), 0, -1);

    return [
        'name' => $name,
        'slug' => str_slug($name)
    ];
});