<?php

use App\Models\Score;
use App\Models\User;
use Faker\Generator;

/* @var Illuminate\Database\Eloquent\Factory $factory */
/*
 * ScoreFactory
 */
$factory->define(Score::class, function (Generator $faker) {
    $createdAt = $faker->dateTimeBetween("-1 year", "now");

    return [
        "game" => "meteor",
        "score" => random_int(1, 10) * 100,
        "user_id" => User::random()->id,
        "data" => null,
        "created_at" => $createdAt,
        "updated_at" => $createdAt
    ];
});
