<?php

use App\Models\Activity;
use App\Models\Module;
use Faker\Generator;
use Illuminate\Database\Eloquent\Factory;

/**
 * ModuleFactory.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0
 *
 * @var Factory $factory
 */
$factory->define(Module::class, function (Generator $faker) {
    [$createdAt, $updatedAt] = timestamps();

    return [
        "premium" => $faker->boolean(70),
        "visible" => $faker->boolean(95),
        "seo_index" => $faker->boolean(30),
        "seo_title" => substr($faker->sentence, 0, -1),
        "seo_keywords" => implode(",", $faker->words),
        "seo_description" => $faker->sentence,
        "order" => $faker->numberBetween(0, 100),
        "activity_id" => Activity::random()->id,
        "created_at" => $createdAt,
        "updated_at" => $updatedAt
    ];
});

/**
 * ModuleRandomFactory.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0
 *
 * @var Factory $factory
 */
$factory->defineAs(Module::class, "random", function (Generator $faker) use ($factory) {
    return $factory->rawOf(Module::class, $faker->randomElement([
        "course",
        "fill",
        "mcq",
        "mcq-table",
        "test",
        "flashcard",
    ]));
});
