<?php

declare(strict_types=1);

use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use Railgun\Models\Exercise;
use Railgun\Models\Subject;
use Railgun\Support\Silicate;
use Railgun\Support\Timestamps;

/**
 * @var Factory $factory
 */
$factory->define(Exercise::class, static function (Faker $faker) {
    $timestamps = Timestamps::factory();
    $subject    = Subject::random();

    return [
        'content'    => Silicate::wall(),
        'order'      => 99,
        'premium'    => $subject->premium,
        'visible'    => $faker->boolean(90),
        'subject_id' => $subject->id,
        'created_at' => $timestamps->createdAt,
        'updated_at' => $timestamps->updatedAt,
    ];
});
