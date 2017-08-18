<?php
use App\Models\Module;
use Faker\Generator;

/**
 * ModuleMCQFactory
 * @author Mathrix Team
 * @var Illuminate\Database\Eloquent\Factory $factory
 */
$factory->defineAs(Module::class, 'mcq', function (Generator $faker) use ($factory) {
    $questionCount = $faker->numberBetween(1, 5);
    $questions = [];
    $choices = [];
    $solutions = [];
    $corrections = [];

    for ($q = 0; $q < $questionCount; $q++) {
        $choiceCount = $faker->numberBetween(2, 4);
        $questionChoices = $faker->words($choiceCount);

        $questions[] = str_replace('.', '?', $faker->sentence());
        $choices[] = $questionChoices;
        $solutions[] = $faker->randomElements($questionChoices, $faker->numberBetween(1, $choiceCount));
        $corrections[] = $faker->paragraphs($faker->numberBetween(1, 3));
    }

    $data = [
        'type' => 'mcq',
        'questions' => $questions,
        'choices' => $choices,
        'solutions' => $solutions,
        'corrections' => $corrections
    ];

    return array_merge($factory->raw(Module::class), $data);
});