<?php

declare(strict_types=1);

use Railgun\Models\Chapter;
use Railgun\Models\Discipline;
use Railgun\Models\Grade;
use Railgun\Support\Timestamps;
use Faker\Generator;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Str;

/**
 * @var Factory $factory
 */
$factory->define(Chapter::class, static function (Generator $faker) {
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
