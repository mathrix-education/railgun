<?php

use App\Models\Class_;
use App\Models\Level;
use App\Models\User;
use Faker\Generator;
use Illuminate\Support\Facades\Hash;

/*
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
            "sound" => $faker->boolean(70),
        ],
        "class_id" => Class_::inRandomOrder()
            ->firstOrFail()->id,
        "created_at" => $faker->dateTimeBetween("-1 years"),
    ];

    if ($faker->boolean(50)) {
        $data["settings"]["facebook_id"] = $faker->password();
    }

    return $data;
});

/*
 * UserRegisteredFactory
 *
 * @author Mathrix Team
 * @var Illuminate\Database\Eloquent\Factory $factory
 */
$factory->defineAs(User::class, "registered", function () use ($factory) {
    return array_merge($factory->raw(User::class), ["type" => "registered"]);
});

/*
 * UserRedactorFactory
 *
 * @author Mathrix Team
 * @var Illuminate\Database\Eloquent\Factory $factory
 */
$factory->defineAs(User::class, "redactor", function (Generator $faker) use ($factory) {
    $data = [
        "username" => $faker->userName,
        "firstname" => $faker->firstName,
        "lastname" => $faker->lastName,
        "type" => "redactor",
        "class_id" => null,
        "stripe_id" => null,
    ];

    return array_merge($factory->raw(User::class), $data);
});

/*
 * UserModeratorFactory
 *
 * @author Mathrix Team
 * @var Illuminate\Database\Eloquent\Factory $factory
 */
$factory->defineAs(User::class, "moderator", function () use ($factory) {
    return array_merge($factory->rawOf(User::class, "redactor"), ["type" => "moderator"]);
});

/*
 * UserAdminFactory
 *
 * @author Mathrix Team
 * @var Illuminate\Database\Eloquent\Factory $factory
 */
$factory->defineAs(User::class, "admin", function () use ($factory) {
    return array_merge($factory->rawOf(User::class, "redactor"), ["type" => "admin"]);
});

/*
 * UserSupportFactory
 *
 * @author Mathrix Team
 * @var Illuminate\Database\Eloquent\Factory $factory
 */
$factory->defineAs(User::class, "support", function () use ($factory) {
    return array_merge($factory->rawOf(User::class, "redactor"), ["type" => "support"]);
});
