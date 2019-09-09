<?php

declare(strict_types=1);

use App\Models\Module;
use Faker\Generator;
use Illuminate\Database\Eloquent\Factory;

/**
 * ModuleTestFactory.
 *
 * @var Factory $factory
 */
$factory->defineAs(Module::class, 'test', static function (Generator $faker) use ($factory) {
    $data = $factory->rawOf(Module::class, 'mcq');

    $data['choices']   = null;
    $data['solutions'] = null;

    $data['type']    = 'test';
    $data['options'] = [
        'duration' => $faker->randomElement([10, 15, 20, 25, 30]) * 60,
    ];

    return $data;
});
