<?php

declare(strict_types=1);

use App\Models\Module;
use Faker\Generator;
use Illuminate\Database\Eloquent\Factory;

/**
 * ModuleFactory.
 *
 * @var Factory $factory
 */
$factory->define(Module::class, static function (Generator $faker) use ($factory) {
    return $factory->rawOf(Module::class, $faker->randomElement([
        'course',
        'mcq',
        'test',
        'flashcard',
    ]));
});
