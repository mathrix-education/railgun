<?php

declare(strict_types=1);

use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Str;
use Railgun\Models\Chapter;
use Railgun\Models\Flashcard;
use Railgun\Support\Silicate;
use Railgun\Support\Timestamps;

/**
 * @var Factory $factory
 */
$factory->define(Flashcard::class, static function (Faker $faker) {
    $name    = $faker->words(3, true);
    $content = collect(range(0, $faker->numberBetween(5, 10)))
        ->map(static fn() => [
            'front' => Silicate::wall(),
            'back'  => Silicate::wall(),
        ])
        ->toArray();

    $timestamps = Timestamps::factory();

    return [
        'name'       => Str::ucfirst($name),
        'content'    => $content,
        'order'      => 99,
        'premium'    => $faker->boolean(90),
        'protected'  => false,
        'visible'    => $faker->boolean(90),
        'chapter_id' => Chapter::random()->id,
        'created_at' => $timestamps->createdAt,
        'updated_at' => $timestamps->updatedAt,
    ];
});
