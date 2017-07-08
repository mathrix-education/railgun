<?php
$factory->define(App\Models\Module::class, function (Faker\Generator $faker) {
    $questions = [];
    $choices = [];
    $solutions = [];
    $corrections = [];

    $questionCount = $faker->numberBetween(1, 5);

    for ($q = 0; $questionCount < $questionCount; $q++) {
        $choiceCount = $faker->numberBetween(2, 4);

        $questions[] = substr($faker->sentence(), -1, 1) . '?';
        $choices[] = $faker->words($choiceCount);
        $solutions[] = $faker->words($choiceCount);
        $corrections[] = $faker->paragraphs(
            $faker->numberBetween(2, 3)
        );
    }

    return [
        'name' => $faker->sentence(3),
        'type' => $faker->randomElement(['mcq', 'match', 'fill']),
        'questions' => $questions,
        'choices' => $choices,
        'solutions' => $solutions,
        'correction' => $corrections
    ];
});