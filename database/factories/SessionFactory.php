<?php

declare(strict_types=1);

use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use Railgun\Models\Chapter;
use Railgun\Models\Session;
use Railgun\Models\User;

/**
 * @var Factory $factory
 */
$factory->define(Session::class, static function (Faker $faker) {
    do {
        $chapter = Chapter::random();

        $key = [
            'user_id'       => User::random()->id,
            'discipline_id' => $chapter->theme->discipline_id,
        ];
    } while (Session::query()->where($key)->exists());

    return array_merge($key, [
        'id'         => $faker->uuid,
        'chapter_id' => $chapter->id,
        'updated_at' => $faker->dateTime,
    ]);
});
