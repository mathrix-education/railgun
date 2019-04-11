<?php

use App\Models\Module;
use Faker\Generator;
use Illuminate\Database\Eloquent\Factory;

/**
 * ModuleMCQFactory.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0
 *
 * @var Factory $factory
 */
$factory->defineAs(Module::class, "mcq", function (Generator $faker) use ($factory) {
    $questionCount = $faker->numberBetween(1, 5);
    $questions = [];
    $choices = [];
    $solutions = [];
    $corrections = [];

    for ($q = 0; $q < $questionCount; ++$q) {
        $questions[] = str_replace(".", "?", $faker->sentence());

        $choiceCount = $faker->numberBetween(2, 4);
        $questionChoices = $faker->words($choiceCount);

        $choices[] = $questionChoices;
        $solutions[] = $faker->randomElements(array_keys($questionChoices), $faker->numberBetween(1, $choiceCount));
        $corrections[] = $faker->paragraphs($faker->numberBetween(1, 3));
    }

    $data = [
        "type" => "mcq",
        "questions" => $questions,
        "choices" => $choices,
        "solutions" => $solutions,
        "corrections" => $corrections,
    ];

    return array_merge($factory->raw(Module::class), $data);
});
