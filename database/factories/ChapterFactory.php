<?php
use App\Models\Chapter;
use Faker\Generator;

/**
 * ChapterFactory
 *
 * @author Mathrix Team
 * @var Illuminate\Database\Eloquent\Factory $factory
 */
$factory->define(Chapter::class, function (Generator $faker) {
    $name = substr($faker->sentence($faker->numberBetween(1, 3)), 0, -1);

    return [
        'name' => $name,
        'slug' => str_slug($name)
    ];
});