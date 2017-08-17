<?php

use App\Models\Class_;
use App\Models\User;
use Faker\Generator;
use Illuminate\Support\Facades\Hash;

/** @var Illuminate\Database\Eloquent\Factory $factory */

$factory->define(User::class, function (Generator $faker) {
    $names = $faker->boolean(30);

    return [
        'username' => $faker->boolean(30) ? $faker->userName : null,
        'email' => $faker->email,
        'firstName' => $names ? $faker->firstName : null,
        'lastName' => $names ? $faker->lastName : null,
        'password' => Hash::make('test'),
        'facebook_token' => null,
        'points' => $faker->numberBetween(0, 100) * 10,
        'settings' => [
            'daily_goal' => $faker->numberBetween(0, 10) * 10,
            'sound' => $faker->boolean(70),
            'email_newsletter' => $faker->boolean(90),
            'email_comment' => $faker->boolean(85)
        ],
        'class_id' => $faker->randomElement(Class_::all()->pluck('id')->toArray())
    ];
});

$factory->defineAs(User::class, 'registered', function (Generator $faker) use ($factory) {
    $data = [
        'type' => 'registered',
    ];

    return array_merge($factory->raw(User::class), $data);
});

$factory->defineAs(User::class, 'premium', function (Generator $faker) use ($factory) {
    $data = [
        'type' => 'premium',
    ];

    return array_merge($factory->raw(User::class), $data);
});

$factory->defineAs(User::class, 'moderator', function (Generator $faker) use ($factory) {
    $data = [
        'username' => $faker->userName,
        'firstName' => $faker->firstName,
        'lastName' => $faker->lastName,
        'type' => 'moderator',
        'class_id' => null
    ];

    return array_merge($factory->raw(User::class), $data);
});

$factory->defineAs(User::class, 'admin', function (Generator $faker) use ($factory) {
    $data = [
        'username' => $faker->userName,
        'firstName' => $faker->firstName,
        'lastName' => $faker->lastName,
        'type' => 'admin',
        'class_id' => null
    ];

    return array_merge($factory->raw(User::class), $data);
});