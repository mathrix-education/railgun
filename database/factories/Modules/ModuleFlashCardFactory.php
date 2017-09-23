<?php
use App\Models\Module;
use Faker\Generator;

/**
 * ModuleFlashCardFactory
 *
 * @author Mathrix Team
 * @var Illuminate\Database\Eloquent\Factory $factory
 */
$factory->defineAs(Module::class, 'flashcard', function (Generator $faker) use ($factory) {
    $data = $factory->rawOf(Module::class, 'mcq');

    $data['choices'] = null;
    $data['solutions'] = null;
    $data['type'] = 'flashcard';

    return $data;
});