<?php
use App\Models\Lesson;
use App\Models\Module;
use App\Models\Section;
use Faker\Generator;

/**
 * LessonFactory
 *
 * @author Mathrix Team
 * @var Illuminate\Database\Eloquent\Factory $factory
 */
$factory->define(Lesson::class, function (Generator $faker) {
    $title = substr($faker->sentence($faker->numberBetween(3, 5)), 0, -1);

    return [
        "title" => $title,
        "slug" => str_slug($title),
        "image" => null,
        "visible" => $faker->boolean,
        "section_id" => Section::inRandomOrder()->first()->id,
        "module_id" => Module::inRandomOrder()->first()->id
    ];
});
