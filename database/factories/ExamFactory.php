<?php

declare(strict_types=1);

use Faker\Generator;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Str;
use Railgun\Models\Chapter;
use Railgun\Models\Exam;
use Railgun\Support\Silicate;
use Railgun\Support\Timestamps;

/**
 * @var Factory $factory
 */
$factory->define(Exam::class, static function (Generator $faker) use ($factory) {
    $type = $faker->randomElement(['video', 'questions']);

    return $factory->rawOf(Exam::class, $type);
});

$factory->defineAs(Exam::class, 'base', static function (Generator $faker): array {
    $name       = ucfirst($faker->word);
    $timestamps = Timestamps::factory();

    return [
        'name'       => $name,
        'slug'       => Str::slug($name),
        'order'      => 99,
        'visible'    => $faker->boolean(90),
        'chapter_id' => Chapter::random()->id,
        'created_at' => $timestamps->createdAt,
        'updated_at' => $timestamps->updatedAt,
    ];
});

$factory->defineAs(Exam::class, 'questions', static function (Generator $faker) use ($factory) {
    return $factory->rawOf(Exam::class, 'base', [
        'duration' => $faker->numberBetween(2, 5) * 10,
        'content'  => Silicate::wall(),
    ]);
});

$factory->defineAs(Exam::class, 'video', static function () use ($factory) {
    return $factory->rawOf(Exam::class, 'base', [
        'duration' => null,
        'content'  => [Silicate::video()],
    ]);
});
