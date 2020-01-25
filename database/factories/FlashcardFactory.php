<?php

declare(strict_types=1);

use App\Models\Chapter;
use App\Models\Flashcard;
use App\Support\Timestamps;
use Faker\Generator;
use Illuminate\Database\Eloquent\Factory;

/**
 * @var Factory $factory
 */
$factory->define(Flashcard::class, static function (Generator $faker) {
    $cardCount = $faker->numberBetween(5, 10);
    $fronts    = [];
    $backs     = [];

    for ($i = 0; $i < $cardCount; $i++) {
        $fronts[] = $faker->sentence;
        $backs[]  = $faker->sentence;
    }

    $timestamps = Timestamps::factory();

    return [
        'fronts'     => $fronts,
        'backs'      => $backs,
        'visible'    => $faker->boolean(90),
        'chapter_id' => Chapter::random()->id,
        'created_at' => $timestamps->createdAt,
        'updated_at' => $timestamps->updatedAt,
    ];
});
