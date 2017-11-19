<?php

use App\Models\Module;
use Faker\Generator;

/**
 * ModuleFillFactory
 *
 * @author Mathrix Team
 * @var Illuminate\Database\Eloquent\Factory $factory
 */
$factory->defineAs(Module::class, 'fill', function (Generator $faker) use ($factory) {
    $data = $factory->rawOf(Module::class, 'mcq');
    $data['type'] = 'fill';
    $data['questions'] = null;

    // Generate gaps
    $content = $faker->text(100);
    $choiceCount = count($data['choices']);

    for($i = 0; $i < $choiceCount; $i++) {
        $content .= '[choix]' . $faker->text(100);
        $data['choices'][$i][] = $data['choices'][$i][0];
        $data['corrections'][$i] = $data['corrections'][$i][0];
    }

    return $data;
});