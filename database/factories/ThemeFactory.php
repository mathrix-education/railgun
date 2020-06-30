<?php

declare(strict_types=1);

use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Str;
use Railgun\Models\Discipline;
use Railgun\Models\Grade;
use Railgun\Models\Theme;
use Railgun\Support\Timestamps;

/**
 * @var Factory $factory
 */
$factory->define(Theme::class, static function (Faker $faker) {
    $name       = ucfirst($faker->word);
    $timestamps = Timestamps::factory();

    return [
        'name'          => $name,
        'slug'          => Str::slug($name),
        'order'         => 99,
        'visible'       => $faker->boolean(95),
        'grade_id'      => Grade::random()->id,
        'discipline_id' => Discipline::random()->id,
        'created_at'    => $timestamps->createdAt,
        'updated_at'    => $timestamps->updatedAt,
    ];
});
