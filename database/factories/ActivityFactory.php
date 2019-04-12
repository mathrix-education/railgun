<?php

use App\Models\Activity;
use App\Models\Notion;
use Faker\Generator;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Str;

/**
 * ActivityFactory.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 *
 * @var Factory $factory
 */
$factory->define(Activity::class, function (Generator $faker) {
    $name = mb_substr($faker->sentence(), 0, -1);
    [$createdAt, $updatedAt] = timestamps();

    return [
        "name" => $name,
        "slug" => Str::slug($name),
        "type" => $faker->randomElement(Activity::TYPES),
        "visible" => $faker->boolean(95),
        "order" => $faker->numberBetween(0, 100),
        "premium" => $faker->boolean(30),
        "reviewed" => $faker->boolean(),
        "notion_id" => Notion::random()->id,
        "created_at" => $createdAt,
        "updated_at" => $updatedAt
    ];
});
