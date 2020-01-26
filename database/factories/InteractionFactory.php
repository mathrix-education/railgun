<?php

declare(strict_types=1);

use Railgun\Models\Course;
use Railgun\Models\Exam;
use Railgun\Models\Exercise;
use Railgun\Models\Flashcard;
use Railgun\Models\Interaction;
use Railgun\Models\User;
use Railgun\Support\Timestamps;
use Faker\Generator;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Str;

/**
 * @var Factory $factory
 */
$factory->define(Interaction::class, static function (Generator $faker) {
    $class = $faker->randomElement([Course::class, Exercise::class, Flashcard::class, Exam::class]);
    /** @var Course|Exercise|Flashcard|Exam $model */
    $model = $class::random();

    $timestamps = Timestamps::factory();

    $data = [
        'user_id'           => User::random()->id,
        'interactable_type' => Str::slug(class_basename($class), '_'),
        'interactable_id'   => $model->id,
        'created_at'        => $timestamps->createdAt,
        'updated_at'        => $timestamps->updatedAt,
    ];

    switch ($class) {
        case Course::class:
            $data += [
                'comprehension' => $faker->randomElement(Interaction::COMPREHENSIONS),
                'score'         => null,
            ];
            break;
        case Exercise::class:
            $data += [
                'comprehension' => $faker->randomElement(Interaction::COMPREHENSIONS),
                'score'         => $faker->boolean ? 20 : 0,
            ];
            break;
        case Flashcard::class:
            $data += [
                'comprehension' => null,
                'score'         => null,
            ];
            break;
        case Exam::class:
            $data += [
                'comprehension' => null,
                'score'         => $faker->numberBetween(5, 20),
            ];
            break;
    }

    return $data;
});
