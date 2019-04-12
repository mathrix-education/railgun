<?php

use App\Models\Chapter;
use App\Models\Discipline;
use App\Models\Grade;
use Faker\Generator;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Str;

/**
 * ChapterFactory.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 *
 * @var Factory $factory
 */
$factory->define(Chapter::class, function (Generator $faker) {
    $name = mb_substr($faker->sentence($faker->numberBetween(1, 3)), 0, -1);
    [$createdAt, $updatedAt] = timestamps();

    return [
        "name" => $name,
        "slug" => Str::slug($name),
        "order" => $faker->numberBetween(0, 100),
        "visible" => $faker->boolean(95),
        "grade_id" => Grade::random()->id,
        "discipline_id" => Discipline::random()->id,
        "created_at" => $createdAt,
        "updated_at" => $updatedAt
    ];
});
