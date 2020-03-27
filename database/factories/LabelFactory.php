<?php

declare(strict_types=1);

use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use Railgun\Models\Label;
use Railgun\Support\Timestamps;

/**
 * @var Factory $factory
 */
$factory->define(Label::class, static function (Faker $faker) {
    $timestamps = Timestamps::factory();

    return [
        'name'       => $faker->word,
        'color'      => $faker->hexColor,
        'created_at' => $timestamps->createdAt,
        'updated_at' => $timestamps->updatedAt,
    ];
});
