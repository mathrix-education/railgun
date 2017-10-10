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

    $data['type'] = 'flashcard';

    $data['choices'] = null;
    $data['solutions'] = null;

    foreach ($data['corrections'] as $k => $v) {
        $data['corrections'][$k] = $v[0];
    }

    return $data;
});