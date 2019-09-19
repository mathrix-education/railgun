<?php

declare(strict_types=1);

use App\Models\Activity;
use App\Models\Module;
use Faker\Generator;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Str;

/**
 * ModuleCourseFactory.
 *
 * @var Factory $factory
 */
$factory->defineAs(Module::class, 'base', static function (Generator $faker) {
    [$createdAt, $updatedAt] = timestamps();
    $seoTitle                = substr($faker->sentence, 0, -1);

    return [
        'visible' => $faker->boolean(95),
        'seo_index' => $faker->boolean(30),
        'seo_title' => $seoTitle,
        'seo_slug' => Str::slug($seoTitle),
        'seo_keywords' => implode(',', $faker->words),
        'seo_description' => $faker->sentence,
        'order' => $faker->numberBetween(0, 100),
        'activity_id' => Activity::random()->id,
        'created_at' => $createdAt,
        'updated_at' => $updatedAt,
    ];
});
