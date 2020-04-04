<?php

declare(strict_types=1);

use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use Railgun\Models\Exercise;
use Railgun\Models\Subject;
use Railgun\Support\Silicate;
use Railgun\Support\Timestamps;

/**
 * @var Factory $factory
 */
$factory->define(Exercise::class, static function (Faker $faker) {
    $questionCount = $faker->numberBetween(2, 4);
    $questions     = [];
    $choices       = [];
    $solutions     = [];
    $explanations  = [];

    for ($i = 0; $i < $questionCount; $i++) {
        // questions
        $questions[] = Silicate::wall($faker->numberBetween(1, 3));

        // choices
        $localChoices = [];
        $choiceCount  = $faker->numberBetween(2, 4);
        for ($j = 0; $j < $choiceCount; $j++) {
            $localChoices[] = Silicate::wall($faker->numberBetween(1, 3));
        }
        $choices[]   = $localChoices;
        $solutions[] = $faker->numberBetween(0, $choiceCount - 1);

        // explanations
        $localExplanations = [];
        $explanationCount  = $faker->numberBetween(2, 4);
        for ($j = 0; $j < $explanationCount; $j++) {
            $localExplanations[] = Silicate::wall($faker->numberBetween(1, 3));
        }
        $explanations[] = $localExplanations;
    }

    $timestamps = Timestamps::factory();

    return [
        'type'         => 'mcq',
        'questions'    => $questions,
        'choices'      => $choices,
        'solutions'    => $solutions,
        'explanations' => $explanations,
        'order'        => 99,
        'premium'      => $faker->boolean(90),
        'visible'      => $faker->boolean(90),
        'subject_id'   => Subject::random()->id,
        'created_at'   => $timestamps->createdAt,
        'updated_at'   => $timestamps->updatedAt,
    ];
});
