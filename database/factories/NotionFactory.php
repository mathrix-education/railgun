<?php

use App\Models\Chapter;
use App\Models\Notion;
use Faker\Generator;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Str;

/**
 * NotionFactory.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 *
 * @var Factory $factory
 */
$factory->define(Notion::class, function (Generator $faker) {
    $name = mb_substr($faker->sentence($faker->numberBetween(3, 5)), 0, -1);
    [$createdAt, $updatedAt] = timestamps();

    return [
        "name" => $name,
        "slug" => Str::slug($name),
        "type" => $faker->randomElement(Notion::TYPES),
        "visible" => $faker->boolean(95),
        "order" => $faker->numberBetween(0, 100),
        "chapter_id" => Chapter::random()->id,
        "created_at" => $createdAt,
        "updated_at" => $updatedAt
    ];
});
