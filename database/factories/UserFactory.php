<?php

use App\Models\Grade;
use App\Models\Level;
use App\Models\User;
use Faker\Generator;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

/**
 * UserFactory.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 *
 * @var Factory $factory
 */
$factory->define(User::class, function (Generator $faker) {
    $names = $faker->boolean(30);
    $points = $faker->numberBetween(0, 100) * 10;
    [$createdAt, $updatedAt] = timestamps();

    $data = [
        "username" => !$names ? $faker->userName : null,
        "email" => $faker->email,
        "firstname" => $names ? $faker->firstName : null,
        "lastname" => $names ? $faker->lastName : null,
        "password" => Cache::remember("DB_PASSWORD.hash", 30, function () {
            return Hash::make(env("DB_PASSWORD"));
        }),
        "token" => $faker->uuid,
        "points" => $points,
        "level" => Level::getLevelByPoints($points),
        "daily" => $faker->randomElement([10, 20, 40]),
        "settings" => [
            "sound" => $faker->boolean(70),
        ],
        "grade_id" => Grade::random()->id,
        "created_at" => $createdAt,
        "updated_at" => $updatedAt,
    ];

    if ($faker->boolean(50)) {
        $data["settings"]["facebook_id"] = $faker->password();
    }

    return $data;
});

$additionalFactories = ["registered", "moderator", "admin"];
foreach ($additionalFactories as $type) {
    $factory->defineAs(User::class, $type, function (Generator $faker) use ($factory, $type) {
        switch ($type) {
            case "admin":
                $scopes = ["*"];
                break;
            case "moderator":
                $scopes = ["moderator", "registered"];
                break;
            default:
                $scopes = [$type];
                break;
        }

        $data = [
            "username" => $faker->userName,
            "firstname" => $faker->firstName,
            "lastname" => $faker->lastName,
            "scopes" => $scopes
        ];

        if ($type != "registered") {
            $data = array_merge($data, [
                "grade_id" => null,
                "stripe_id" => null
            ]);
        }

        return array_merge($factory->raw(User::class), $data);
    });
}
