<?php
use App\Models\Module;
use Faker\Generator;

/**
 * ModuleMatchFactory
 * @author Mathrix Team
 * @var Illuminate\Database\Eloquent\Factory $factory
 */
$factory->defineAs(Module::class, 'match', function (Generator $faker) use ($factory) {
    $data = $factory->rawOf(Module::class, 'mcq');

    $data['questions'] = $faker->words(count($data['questions']));
    $data['choices'] = $faker->words(count($data['questions']));

    $solutions = $data['choices'];
    shuffle($solutions);
    $data['solutions'] = array_combine(array_keys($data['questions']), $solutions);

    $data['type'] = 'match';

    return $data;
});