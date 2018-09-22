<?php

use App\Models\Chapter;
use App\Models\Class_;
use App\Models\Discipline;
use Faker\Generator;

/*
 * ChapterFactory
 *
 * @author Mathrix Team
 * @var Illuminate\Database\Eloquent\Factory $factory
 */
$factory->define(Chapter::class, function (Generator $faker) {
    $name = mb_substr($faker->sentence($faker->numberBetween(1, 3)), 0, -1);
    /** @var Class_ $class */
    $class = Class_::inRandomOrder()
        ->firstOrFail();
    /** @var Discipline $discipline */
    $discipline = $class->disciplines()->firstOrFail();

    return [
        "name" => $name,
        "slug" => str_slug($name),
        "class_id" => $class->id,
        "discipline_id" => $discipline->id,
        "visible" => $faker->boolean(95),
    ];
});
