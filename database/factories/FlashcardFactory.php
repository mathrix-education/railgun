<?php

declare(strict_types=1);

use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Str;
use Railgun\Models\Chapter;
use Railgun\Models\Flashcard;
use Railgun\Support\Timestamps;

/**
 * @var Factory $factory
 */
$factory->define(Flashcard::class, static function (Faker $faker) {
    $name      = $faker->words(3, true);
    $cardCount = $faker->numberBetween(5, 10);
    $fronts    = [];
    $backs     = [];

    for ($i = 0; $i < $cardCount; $i++) {
        $fronts[] = $faker->sentence;
        $backs[]  = $faker->sentence;
    }

    $timestamps = Timestamps::factory();

    return [
        'name'       => $name,
        'slug'       => Str::slug($name),
        'fronts'     => $fronts,
        'backs'      => $backs,
        'order'      => 99,
        'premium'    => $faker->boolean(90),
        'visible'    => $faker->boolean(90),
        'chapter_id' => Chapter::random()->id,
        'created_at' => $timestamps->createdAt,
        'updated_at' => $timestamps->updatedAt,
    ];
});
