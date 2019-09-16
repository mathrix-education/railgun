<?php

declare(strict_types=1);

use App\Models\Comment;
use App\Models\User;
use App\Models\Vote;
use Faker\Generator;
use Illuminate\Database\Eloquent\Factory;

/**
 * VoteFactory.
 *
 * @var Factory $factory
 */
$factory->define(Vote::class, static function (Generator $faker) {
    $createdAt = $faker->dateTimeBetween('-1 month');

    do {
        $voteData = [
            'value' => $faker->boolean(70) ? 1 : -1,
            'user_id' => User::random()->id,
            'comment_id' => Comment::random()->id,
            'created_at' => $createdAt,
            'updated_at' => $faker->boolean(90) ? $createdAt : $faker->dateTimeBetween($createdAt, 'now'),
        ];
    } while (Vote::query()
        ->where([
            ['user_id', '=', $voteData['user_id']],
            ['comment_id', '=', $voteData['comment_id']],
        ])
        ->exists());

    return $voteData;
});
