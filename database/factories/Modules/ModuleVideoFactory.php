<?php
use App\Models\Module;
use Faker\Generator;

/**
 * ModuleVideoFactory
 * @author Mathrix Team
 * @var Illuminate\Database\Eloquent\Factory $factory
 */
$factory->defineAs(Module::class, 'video', function (Generator $faker) use ($factory) {
    $cut = $faker->boolean(5);

    $data = [
        'type' => 'video',
        'content' => str_random(11), // The YouTube video id
        'options' => $cut ? [
            'starts' => $faker->numberBetween(0, 60),
            'ends' => $faker->numberBetween(600, 700)
        ] : []
    ];

    return array_merge($factory->raw(Module::class), $data);
});