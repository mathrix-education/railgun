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

    return [
        'username' => $faker->boolean(30) ? $faker->userName : null,
        'email' => $faker->email,
        'firstName' => $names ? $faker->firstName : null,
        'lastName' => $names ? $faker->lastName : null,
        'password' => Hash::make('test'),
        'token' => $faker->uuid,
        'points' => $points,
        'level' => Level::getLevelByPoints($points),
        'daily' => $faker->randomElement([20, 40, 70]),
        'settings' => [
            'daily_goal' => $faker->numberBetween(0, 10) * 10,
            'sound' => $faker->boolean(70),
            'email_newsletter' => $faker->boolean(90),
            'email_comment' => $faker->boolean(85)
        ],
        'class_id' => $faker->randomElement(Class_::all()->pluck('id')->toArray())
    ];
});


/**
 * UserRegisteredFactory
 *
 * @author Mathrix Team
 * @var Illuminate\Database\Eloquent\Factory $factory
 */
$factory->defineAs(User::class, 'registered', function (Generator $faker) use ($factory) {
    $data = [
        'type' => 'registered',
    ];

    return array_merge($factory->raw(User::class), $data);
});


/**
 * UserPremiumFactory
 *
 * @author Mathrix Team
 * @var Illuminate\Database\Eloquent\Factory $factory
 */
$factory->defineAs(User::class, 'premium', function (Generator $faker) use ($factory) {
    $data = [
        'type' => 'premium',
    ];

    return array_merge($factory->raw(User::class), $data);
});


/**
 * UserModeratorFactory
 *
 * @author Mathrix Team
 * @var Illuminate\Database\Eloquent\Factory $factory
 */
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


/**
 * UserAdminFactory
 *
 * @author Mathrix Team
 * @var Illuminate\Database\Eloquent\Factory $factory
 */
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