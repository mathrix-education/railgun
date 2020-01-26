<?php

declare(strict_types=1);

use App\Models\Grade;
use App\Models\User;
use App\Support\Timestamps;
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
    $names      = $faker->boolean(30);
    $timestamps = Timestamps::factory();

    $data = [
        'username'   => !$names ? $faker->userName : null,
        'email'      => $faker->email,
        'firstname'  => $names ? $faker->firstName : null,
        'lastname'   => $names ? $faker->lastName : null,
        'password'   => Cache::remember(
            'DB_PASSWORD.hash',
            30,
            static function () {
                return Hash::make(env('DB_PASSWORD'));
            }
        ),
        'scopes'     => [],
        'token'      => $faker->uuid,
        'settings'   => [
            'sound' => $faker->boolean(70),
        ],
        'grade_id'   => Grade::random()->id,
        'created_at' => $timestamps->createdAt,
        'updated_at' => $timestamps->updatedAt,
    ];

    if ($faker->boolean(50)) {
        $data['settings']['facebook_id'] = md5($faker->password());
    }

    return $data;
});
