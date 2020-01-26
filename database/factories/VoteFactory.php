<?php

declare(strict_types=1);

use App\Models\Comment;
use App\Models\User;
use App\Models\Vote;
use App\Support\Timestamps;
use Faker\Generator;
use Illuminate\Database\Eloquent\Factory;

/**
 * VoteFactory.
 * May generate conflicting composite keys!
 *
 * @var Factory $factory
 */
$factory->define(Vote::class, static function (Generator $faker) {
    $timestamps = Timestamps::factory();

    return [
        'user_id'    => User::random()->id,
        'comment_id' => Comment::random()->id,
        'value'      => $faker->boolean(70) ? 1 : -1,
        'created_at' => $timestamps->createdAt,
        'updated_at' => $timestamps->updatedAt,
    ];
});
