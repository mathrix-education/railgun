<?php
use App\Models\Module;
use Faker\Generator;

/**
 * ModuleFactory & ModuleRandomFactory
 * @author Mathrix Team
 * @var Illuminate\Database\Eloquent\Factory $factory
 */
$factory->define(Module::class, function (Generator $faker) {
    $name = $faker->sentence(3);
    return [
        'name' => $name,
        'slug' => str_slug($name),
        'description' => $faker->sentence()
    ];
});

$factory->defineAs(Module::class, 'random', function (Generator $faker) use ($factory) {
    return $factory->rawOf(Module::class, $faker->randomElement([
        'course',
        'fill',
        'match',
        'mcq',
        'mcq-table',
        'test',
        'flashcard'
    ]));
});