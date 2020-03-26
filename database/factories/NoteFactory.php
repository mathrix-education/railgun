<?php

declare(strict_types=1);

use Faker\Generator;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Str;
use Railgun\Models\Course;
use Railgun\Models\Exam;
use Railgun\Models\Exercise;
use Railgun\Models\Flashcard;
use Railgun\Models\Note;
use Railgun\Models\User;
use Railgun\Support\Silicate;
use Railgun\Support\Timestamps;

/**
 * @var Factory $factory
 */
$factory->define(Note::class, static function (Generator $faker) {
    $class = $faker->randomElement([Course::class, Exercise::class, Flashcard::class, Exam::class]);
    /** @var Course|Exercise|Flashcard|Exam $model */
    $model      = $class::random();
    $timestamps = Timestamps::factory();

    return [
        'content'      => Silicate::text(),
        'brick_id'     => $faker->boolean ? $faker->uuid : null,
        'user_id'      => User::random()->id,
        'notable_type' => Str::slug(class_basename($model), '_'),
        'notable_id'   => $model->id,
        'created_at'   => $timestamps->createdAt,
        'updated_at'   => $timestamps->updatedAt,
        'deleted_at'   => $faker->boolean(10) ? $timestamps->deletedAt : null,
    ];
});
