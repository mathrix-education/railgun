<?php

use App\Models\Class_;
use App\Models\Level;
use App\Models\User;
use Faker\Generator;
use Illuminate\Support\Facades\Hash;

/**
 * UserFactory
 *
 * @author Mathrix Team
 * @var Illuminate\Database\Eloquent\Factory $factory
 */
$factory->define(User::class, function (Generator $faker) {
    $names = $faker->boolean(30);
    $points = $faker->numberBetween(0, 100) * 10;
    $data = [
        "username" => !$names ? $faker->userName : null,
        "email" => $faker->email,
        "firstname" => $names ? $faker->firstName : null,
        "lastname" => $names ? $faker->lastName : null,
        "password" => Hash::make("test"),
        "token" => $faker->uuid,
        "points" => $points,
        "level" => Level::getLevelByPoints($points),
        "daily" => $faker->randomElement([10, 20, 40]),
        "settings" => [
            "sound" => $faker->boolean(70)
        ],
        "class_id" => Class_::inRandomOrder()->firstOrFail()->id,
        "created_at" => $faker->dateTimeBetween("-1 years"),
    ];

    if ($faker->boolean(50)) {
        $data["settings"]["facebook_id"] = $faker->password();
    }

    return $data;
});


/**
 * UserRegisteredFactory
 *
 * @author Mathrix Team
 * @var Illuminate\Database\Eloquent\Factory $factory
 */
$factory->defineAs(User::class, "registered", function () use ($factory) {
    $data = [
        "type" => "registered",
    ];

    return array_merge($factory->raw(User::class), $data);
});

/**
 * UserRegisteredCustomerFactory
 *
 * @author Mathrix Team
 * @var Illuminate\Database\Eloquent\Factory $factory
 */
$factory->defineAs(User::class, "registeredCustomer", function (Generator $faker) use ($factory) {
    $data = [
        "type" => "registered",
        "stripe_id" => "cus_" . substr($faker->md5, 0, 14)
    ];

    return array_merge($factory->raw(User::class), $data);
});



/**
 * UserModeratorFactory
 *
 * @author Mathrix Team
 * @var Illuminate\Database\Eloquent\Factory $factory
 */
$factory->defineAs(User::class, "moderator", function (Generator $faker) use ($factory) {
    $data = [
        "username" => $faker->userName,
        "firstname" => $faker->firstName,
        "lastname" => $faker->lastName,
        "type" => "moderator",
        "class_id" => null,
        "stripe_id" => null
    ];

    return array_merge($factory->raw(User::class), $data);
});


/**
 * UserAdminFactory
 *
 * @author Mathrix Team
 * @var Illuminate\Database\Eloquent\Factory $factory
 */
$factory->defineAs(User::class, "admin", function (Generator $faker) use ($factory) {
    $data = [
        "username" => $faker->userName,
        "firstname" => $faker->firstName,
        "lastname" => $faker->lastName,
        "type" => "admin",
        "class_id" => null,
        "stripe_id" => null
    ];

    return array_merge($factory->raw(User::class), $data);
});
