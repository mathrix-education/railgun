<?php

declare(strict_types=1);

use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Str;
use Railgun\Models\Chapter;
use Railgun\Models\Theme;
use Railgun\Support\Timestamps;

/**
 * @var Factory $factory
 */
$factory->define(Chapter::class, static function (Faker $faker) {
    $name       = ucfirst($faker->words(3, true));
    $timestamps = Timestamps::factory();

    return [
        'name'       => $name,
        'slug'       => Str::slug($name),
        'order'      => 0,
        'visible'    => $faker->boolean(95),
        'theme_id'   => Theme::random()->id,
        'created_at' => $timestamps->createdAt,
        'updated_at' => $timestamps->updatedAt,
    ];
});
