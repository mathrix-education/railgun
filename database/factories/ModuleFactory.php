<?php
use App\Models\Module;
use Faker\Generator;

/**
 * ModuleFactory & ModuleRandomFactory
 *
 * @author Mathrix Team
 * @var Illuminate\Database\Eloquent\Factory $factory
 */
$factory->define(Module::class, function (Generator $faker) {
    return [
        'premium' => $faker->boolean(70),
        'visible' => $faker->boolean(95)
    ];
});

$factory->defineAs(Module::class, 'random', function (Generator $faker) use ($factory) {
    return $factory->rawOf(Module::class, $faker->randomElement([
        'course',
        'fill',
        //'match', // removed by direct instruction of the big boss
        'mcq',
        'mcq-table',
        'test',
        'flashcard'
    ]));
});