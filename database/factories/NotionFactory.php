<?php
$factory->define(App\Models\Notion::class, function(Faker\Generator $faker) {
    $name = substr($faker->sentence($faker->numberBetween(1, 3)), 0, -1);

    return [
        'name' => $name,
        'slug' => str_slug($name)
    ];
});