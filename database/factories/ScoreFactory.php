<?php

use App\Models\Score;
use App\Models\User;
use Faker\Generator;


/**
 * ScoreFactory
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 *
 * @var Illuminate\Database\Eloquent\Factory $factory
 */
$factory->define(Score::class, function (Generator $faker) {
    $createdAt = $faker->dateTimeBetween("-1 year", "now");

    return [
        "game" => "meteor",
        "score" => random_int(1, 1000),
        "rank" => 0,
        "user_id" => User::random()->id,
        "data" => null,
        "created_at" => $createdAt,
        "updated_at" => $createdAt
    ];
});
