<?php

use Illuminate\Support\Facades\Hash;

$factory->define(App\Models\User::class, function (Faker\Generator $faker) {
    $names = $faker->boolean(30);

    return [
        'username' => $faker->boolean(30) ? $faker->userName : null,
        'email' => $faker->email,
        'firstName' => $names ? $faker->firstName : null,
        'lastName' => $names ? $faker->lastName : null,
        'password' => Hash::make('test'),
        'status' => 'registered',
        'facebook_token' => null,
        'points' => $faker->numberBetween(0, 100) * 10,
        'settings' => [
            'daily_goal' => $faker->numberBetween(0, 10) * 10,
            'sound' => $faker->boolean(70),
            'email_newsletter' => $faker->boolean(90),
            'email_comment' => $faker->boolean(85)
        ],
        'class_id' => $faker->randomElement(
            \App\Models\Class_::all()
                ->pluck('id')
                ->toArray()
        )
    ];
});