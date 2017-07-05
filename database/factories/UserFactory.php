<?php
$factory->define(App\Models\User::class, function (Faker\Generator $faker) {
    return [
        'username' => $faker->userName,
        'email' => $faker->email,
        'firstName' => $faker->firstName,
        'lastName' => $faker->lastName,
        'password' => \Illuminate\Support\Facades\Hash::make('test'),
        'status' => $faker->randomElement(['registered', 'premium', 'moderator', 'admin']),
        'facebook_token' => null,
        'points' => $faker->numberBetween(0, 100) * 10,
        'settings' => json_encode([
            'daily_goal' => $faker->numberBetween(0, 10) * 10,
            'sound' => $faker->boolean(70),
            'email_newsletter' => $faker->boolean(90),
            'email_comment' => $faker->boolean(85)
        ]),
        'class_id' => $faker->randomElement(
            \App\Models\Class_::all()
                ->pluck('id')
                ->toArray()
        )
    ];
});