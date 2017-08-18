<?php
use App\Models\Module;
use Faker\Generator;

/**
 * ModuleMCQTableFactory
 * @author Mathrix Team
 * @var Illuminate\Database\Eloquent\Factory $factory
 */
$factory->defineAs(Module::class, 'mcq-table', function (Generator $faker) use ($factory) {
    $data = $factory->rawOf(Module::class, 'mcq');

    $data['choices'] = $data['choices'][0];

    foreach ($data['solutions'] as $k => $v) {
        $data['solutions'][$k] = $faker->randomElements(
            $data['choices'],
            $faker->numberBetween(1, count($data['choices']))
        );
    }

    $data['type'] = 'mcq-table';

    return $data;
});