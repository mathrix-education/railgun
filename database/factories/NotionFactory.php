<?php
use App\Models\Chapter;
use App\Models\Notion;
use Faker\Generator;

/**
 * NotionFactory
 *
 * @author Mathrix Team
 * @var Illuminate\Database\Eloquent\Factory $factory
 */
$factory->define(Notion::class, function (Generator $faker) {
    $name = substr($faker->sentence($faker->numberBetween(1, 3)), 0, -1);

    return [
        "name" => $name,
        "slug" => str_slug($name),
        "type" => $faker->randomElement(Notion::TYPES),
        "visible" => $faker->boolean(95)
    ];
});