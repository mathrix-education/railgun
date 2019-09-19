<?php

declare(strict_types=1);

use App\Models\Grade;
use App\Models\User;
use Faker\Generator;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

/**
 * UserFactory.
 *
 * @var Factory $factory
 */
$factory->define(User::class, static function (Generator $faker) {
    $names                   = $faker->boolean(30);
    $points                  = $faker->numberBetween(0, 100) * 10;
    [$createdAt, $updatedAt] = timestamps();

    $data = [
        'username' => ! $names ? $faker->userName : null,
        'email' => $faker->email,
        'firstname' => $names ? $faker->firstName : null,
        'lastname' => $names ? $faker->lastName : null,
        'password' => Cache::remember('DB_PASSWORD.hash', 30, static function () {
            return Hash::make(env('DB_PASSWORD'));
        }),
        'scopes' => [],
        'token' => $faker->uuid,
        'points' => $points,
        'daily' => $faker->randomElement([10, 20, 40]),
        'settings' => [
            'sound' => $faker->boolean(70),
        ],
        'grade_id' => Grade::random()->id,
        'created_at' => $createdAt,
        'updated_at' => $updatedAt,
    ];

    if ($faker->boolean(50)) {
        $data['settings']['facebook_id'] = $faker->password();
    }

    return $data;
});
