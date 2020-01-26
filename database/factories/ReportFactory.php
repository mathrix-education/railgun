<?php

declare(strict_types=1);

use App\Models\Course;
use App\Models\Exam;
use App\Models\Exercise;
use App\Models\Flashcard;
use App\Models\Report;
use App\Support\Timestamps;
use Faker\Generator;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Str;

/**
 * @var Factory $factory
 */
$factory->define(Report::class, static function (Generator $faker) {
    $class = $faker->randomElement([Course::class, Exercise::class, Flashcard::class, Exam::class]);
    /** @var Course|Exercise|Flashcard|Exam $model */
    $model      = $class::random();
    $timestamps = Timestamps::factory();

    return [
        'type'            => 'other',
        'content'         => $faker->sentence(10),
        'status'          => $faker->randomElement(Report::STATUSES),
        'reportable_type' => Str::slug(class_basename($class), '_'),
        'reportable_id'   => $model->id,
        'created_at'      => $timestamps->createdAt,
        'updated_at'      => $timestamps->updatedAt,
    ];
});
