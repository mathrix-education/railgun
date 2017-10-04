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

    // Generate gaps
    $content = $faker->text(500);
    $choiceCount = count($data['choices']);
    $gapCount = $faker->numberBetween(1, $choiceCount - 1);
    $explodedContent = explode(' ', $content);
    for($i = 0; $i < $gapCount; $i++) {
        $explodedContent[$faker->numberBetween(0, count($explodedContent) - 1)] = '[choix]';
    }
    $data['content'] = implode(' ', $explodedContent);

    $data['solutions'] = null;

    foreach ($data['corrections'] as $k => $v) {
        $data['corrections'][$k] = $v[0];
    }

    $data['type'] = 'fill';

    return $data;
});