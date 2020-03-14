<?php

declare(strict_types=1);

use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Railgun\Models\Grade;
use Railgun\Models\User;
use Railgun\Support\Timestamps;

/**
 * UserFactory.
 *
 * @var Factory $factory
 */
$factory->define(User::class, static function (Faker $faker) {
    $names      = $faker->boolean(30);
    $timestamps = Timestamps::factory();

    $data = [
        'username'   => !$names ? $faker->userName : null,
        'email'      => $faker->email,
        'firstname'  => $names ? $faker->firstName : null,
        'lastname'   => $names ? $faker->lastName : null,
        'password'   => Cache::remember('DB_PASSWORD.hash', 30, fn() => Hash::make(env('DB_PASSWORD'))),
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
