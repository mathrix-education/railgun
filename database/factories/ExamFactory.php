<?php

declare(strict_types=1);

use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Str;
use Railgun\Models\Chapter;
use Railgun\Models\Exam;
use Railgun\Support\Silicate;
use Railgun\Support\Timestamps;

/**
 * @var Factory $factory
 */
$factory->define(Exam::class, static function (Faker $faker) {
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

$factory->state(Exam::class, 'questions', static function (Faker $faker) {
    return [
        'duration' => $faker->numberBetween(2, 5) * 10,
        'content'  => Silicate::wall(),
    ];
});

$factory->state(Exam::class, 'video', [
    'duration' => null,
    'content'  => [Silicate::video()],
]);

$factory->state(Exam::class, 'random', static function (Faker $faker) {
    $type = $faker->randomElement(['video', 'questions']);

    return factory(Exam::class)->states($type)->raw();
});
