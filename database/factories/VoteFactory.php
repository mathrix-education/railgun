<?php

declare(strict_types=1);

use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Facades\DB;
use Railgun\Models\Comment;
use Railgun\Models\User;
use Railgun\Models\Vote;
use Railgun\Support\Timestamps;

/**
 * VoteFactory.
 * May generate conflicting composite keys!
 *
 * @var Factory $factory
 */
$factory->define(Vote::class, static function (Faker $faker): array {
    $timestamps = Timestamps::factory();

    $key = [
        'user_id'    => User::random()->id,
        'comment_id' => Comment::random()->id,
    ];

    if (DB::table('votes')->where($key)->exists()) {
        return factory(Vote::class)->raw();
    }

    return array_merge($key, [
        'id'         => $faker->uuid,
        'user_id'    => User::random()->id,
        'comment_id' => Comment::random()->id,
        'value'      => $faker->boolean(70) ? 1 : -1,
        'created_at' => $timestamps->createdAt,
        'updated_at' => $timestamps->updatedAt,
    ]);
});
