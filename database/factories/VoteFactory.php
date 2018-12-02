<?php

use App\Models\Comment;
use App\Models\User;
use App\Models\Vote;
use Faker\Generator;

/**
 * VoteFactory
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 *
 * @var Illuminate\Database\Eloquent\Factory $factory
 */
$factory->define(Vote::class, function (Generator $faker) {
    $createdAt = $faker->dateTimeBetween("-1 month");

    do {
        $voteData = [
            "value" => $faker->boolean(70) ? 1 : -1,
            "user_id" => User::random()->id,
            "votable_id" => Comment::random()->id,
            "votable_type" => "comment",
            "created_at" => $createdAt,
            "updated_at" => $faker->boolean(90) ? $createdAt : $faker->dateTimeBetween($createdAt, "now")
        ];
    } while (Vote::where([
        ["user_id", "=", $voteData["user_id"]],
        ["votable_id", "=", $voteData["votable_id"]],
        ["votable_type", "=", $voteData["votable_type"]],
    ])
        ->exists());

    return $voteData;
});
