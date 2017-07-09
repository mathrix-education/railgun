<?php
/**
 * Base factory
 */
use Illuminate\Support\Facades\DB;

$factory->define(App\Models\Activity::class, function (Faker\Generator $faker) {
    $name = substr($faker->sentence(), 0, -1);

    return [
        'name' => $name,
        'slug' => str_slug($name),
        'description' => $faker->sentence()
    ];
});

/**
 * Course factory
 */
$factory->defineAs(App\Models\Activity::class, 'course', function (Faker\Generator $faker) use ($factory) {
    $activity = $factory->raw(App\Models\Activity::class);

    return array_merge($activity, [
        'type' => 'course',
        'content' => implode("\n\n", $faker->paragraphs($faker->numberBetween(3, 8)))
    ]);
});

/**
 * Exercise factory
 */
$factory->defineAs(App\Models\Activity::class, 'exercise', function (Faker\Generator $faker) use ($factory) {
    // Create relationship with modules
    $moduleCount = $faker->numberBetween(2, 8);
    $modulesIds = DB::table('activity_module')->get()->pluck('id');

    for ($i = 0; $i < $moduleCount; $i++) {
        DB::table('activity_module');
    }

    $activity = $factory->raw(App\Models\Activity::class);

    return array_merge($activity, [
        'type' => 'exercise'
    ]);
});

/**
 * Test factory
 */
$factory->defineAs(App\Models\Activity::class, 'test', function (Faker\Generator $faker) use ($factory) {
    $activity = $factory->raw(App\Models\Activity::class);

    $questionCount = $faker->numberBetween(3, 8);

    return array_merge($activity, [
        'type' => 'test',
        'content' => implode("\n\n", $faker->paragraphs($questionCount)),
        'correction' => implode("\n\n", $faker->paragraphs($questionCount)),
        'options' => [
            'duration' => ($faker->randomElement([10, 15, 20, 25, 30])) * 60
        ]
    ]);
});

/**
 * Video factory
 */
$factory->defineAs(App\Models\Activity::class, 'video', function (Faker\Generator $faker) use ($factory) {
    $activity = $factory->raw(App\Models\Activity::class);
    $cut = $faker->boolean(5);

    return array_merge($activity, [
        'type' => 'video',
        'content' => str_random(11),
        'options' => $cut ? [
            'starts' => $faker->numberBetween(0, 60),
            'ends' => $faker->numberBetween(600, 700)
        ] : []
    ]);
});