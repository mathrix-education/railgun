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
 * @since 5.0.0
 *
 * @var Factory $factory
 */
$factory->define(Chapter::class, function (Generator $faker) {
    /** @var Grade $grade */
    $grade = Grade::random();
    /** @var Discipline $discipline */
    $discipline = $grade->disciplines()
        ->inRandomOrder()
        ->firstOrFail();

    $name = mb_substr($faker->sentence($faker->numberBetween(1, 3)), 0, -1);
    [$createdAt, $updatedAt] = timestamps();

    return [
        "name" => $name,
        "slug" => Str::slug($name),
        "order" => 0,
        "visible" => $faker->boolean(95),
        "grade_id" => $grade->id,
        "discipline_id" => $discipline->id,
        "created_at" => $createdAt,
        "updated_at" => $updatedAt
    ];
});
