<?php

declare(strict_types=1);

use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Str;
use Railgun\Models\Course;
use Railgun\Models\Exam;
use Railgun\Models\Exercise;
use Railgun\Models\Flashcard;
use Railgun\Models\Report;
use Railgun\Support\Timestamps;

/**
 * @var Factory $factory
 */
$factory->define(Report::class, static function (Faker $faker) {
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
