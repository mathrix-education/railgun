<?php
use App\Models\Module;
use Faker\Generator;

/**
 * ModuleCourseFactory
 *
 * @author Mathrix Team
 * @var Illuminate\Database\Eloquent\Factory $factory
 */
$factory->defineAs(Module::class, 'course', function (Generator $faker) use ($factory) {
    $data = [
        'type' => 'course',
        'content' => implode("\n\n", $faker->paragraphs($faker->numberBetween(3, 8))),
    ];

    return array_merge($factory->raw(Module::class), $data);
});