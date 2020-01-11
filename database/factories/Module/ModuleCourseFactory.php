<?php

declare(strict_types=1);

use App\Models\Module;
use Faker\Generator;
use Illuminate\Database\Eloquent\Factory;

/**
 * ModuleCourseFactory.
 *
 * @var Factory $factory
 */
$factory->defineAs(Module::class, 'course', static function (Generator $faker) use ($factory) {
    $content  = [];
    $hasVideo = $faker->boolean(80);

    if ($hasVideo) {
        $videoCount = $faker->numberBetween(1, 3);
        for ($i = 0; $i < $videoCount; ++$i) {
            $content[] = '[video]FNUTWw17rkM[/video]';
            /** @noinspection SlowArrayOperationsInLoopInspection */
            $content = array_merge($content, $faker->paragraphs($faker->numberBetween(1, 3)));
        }
    } else {
        $content = $faker->paragraphs($faker->numberBetween(3, 8));
    }

    $data = [
        'type' => 'course',
        'content' => implode("\n\n", $content),
    ];

    return array_merge($factory->rawOf(Module::class, 'base'), $data);
});
