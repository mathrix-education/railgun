<?php

use App\Models\Chapter;
use App\Models\Notion;
use Faker\Generator;
use Illuminate\Database\Eloquent\Factory;

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
    [$createdAt, $updatedAt] = timestamps();

    return [
        "name" => mb_substr($faker->sentence(), 0, -1),
        "type" => $faker->randomElement(Notion::TYPES),
        "visible" => $faker->boolean(95),
        "order" => $faker->numberBetween(0, 100),
        "chapter_id" => Chapter::random()->id,
        "created_at" => $createdAt,
        "updated_at" => $updatedAt
    ];
});
