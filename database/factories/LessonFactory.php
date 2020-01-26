<?php

declare(strict_types=1);

use App\Models\Lesson;
use App\Models\Section;
use App\Support\Silicate;
use App\Support\Timestamps;
use Faker\Generator;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Str;

/**
 * @var Factory $factory
 */
$factory->define(Lesson::class, static function (Generator $faker) {
    $name       = ucfirst($faker->word);
    $timestamps = Timestamps::factory();

    return [
        'name'       => $name,
        'slug'       => Str::slug($name),
        'content'    => Silicate::wall(),
        'order'      => 99,
        'visible'    => $faker->boolean(90),
        'section_id' => Section::random()->id,
        'created_at' => $timestamps->createdAt,
        'updated_at' => $timestamps->updatedAt,
    ];
});
