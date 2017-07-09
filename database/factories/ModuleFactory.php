<?php
$factory->define(App\Models\Module::class, function (Faker\Generator $faker) {
    $type = $faker->randomElement(['mcq', 'mcq-table', 'match', 'fill']);

    $questions = [];
    $choices = [];
    $solutions = [];
    $corrections = [];

    $questionCount = $faker->numberBetween(1, 5);

    for ($q = 0; $q < $questionCount; $q++) {
        $choiceCount = $faker->numberBetween(2, 4);

        $questionChoices = $faker->words($choiceCount);

        $questions[] = str_replace('.', '?', $faker->sentence());

        if ($type != 'mcq-table') {
            $choices[] = $questionChoices;
        } else if (empty($choices)) {
            $choices = $questionChoices;
        }

        $solutions[] = $faker->randomElements(
            $questionChoices,
            $faker->numberBetween(
                1,
                $type != 'fill' ? $choiceCount : 1 // Fill module can't have multiple answers
            )
        );

        $corrections[] = $faker->paragraphs(
            $faker->numberBetween(2, 3)
        );
    }

    $module = [
        'name' => $faker->sentence(3),
        'type' => $type,
        'questions' => $questions,
        'choices' => $choices,
        'solutions' => $solutions,
        'corrections' => $corrections
    ];

    return $module;
});