<?php
use App\Models\Module;
use Faker\Generator;

/**
 * ModuleFillFactory
 * @author Mathrix Team
 * @var Illuminate\Database\Eloquent\Factory $factory
 */
$factory->defineAs(Module::class, 'fill', function (Generator $faker) use ($factory) {
    $data = $factory->rawOf(Module::class, 'mcq');

    foreach ($data['solutions'] as $k => $v) {
        $data['solutions'][$k] = $faker->randomElement($data['choices'][$k]);
    }

    $data['type'] = 'fill';

    return $data;
});