<?php

declare(strict_types=1);

use App\Models\Chapter;
use App\Models\Exam;
use App\Support\Silicate;
use App\Support\Timestamps;
use Faker\Generator;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Str;

/**
 * @var Factory $factory
 */
$factory->define(Exam::class, static function (Generator $faker) use ($factory) {
    $type = $faker->randomElement(['video', 'questions']);

    return $factory->rawOf(Exam::class, $type);
});

$factory->defineAs(Exam::class, 'base', static function (Generator $faker): array {
    $name       = $faker->word;
    $timestamps = Timestamps::factory();

    return [
        'name'       => $name,
        'slug'       => Str::slug($name),
        'visible'    => $faker->boolean(90),
        'chapter_id' => Chapter::random()->id,
        'created_at' => $timestamps->createdAt,
        'updated_at' => $timestamps->updatedAt,
    ];
});

$factory->defineAs(Exam::class, 'questions', static function (Generator $faker) use ($factory) {
    return $factory->rawOf(Comment::class, 'base', [
        'duration' => $faker->numberBetween(2, 5) * 10,
        'content'  => Silicate::wall(),
    ]);
});

$factory->defineAs(Exam::class, 'video', static function () use ($factory) {
    return $factory->rawOf(Comment::class, 'base', [
        'duration' => null,
        'content'  => [
            Silicate::video(),
        ],
    ]);
});
