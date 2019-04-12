<?php

use App\Models\Setting;
use Faker\Generator;
use Illuminate\Database\Eloquent\Factory;

/**
 * SettingFactory.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 *
 * @var Factory $factory
 */
$factory->define(Setting::class, function (Generator $faker) {
    return [
        "key" => $faker->uuid,
        "value" => $faker->sha1,
        "visible" => $faker->boolean,
    ];
});
