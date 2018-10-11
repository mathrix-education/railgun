<?php

use App\Models\Setting;
use Faker\Generator;

/**
 * SettingFactory
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 *
 * @var Illuminate\Database\Eloquent\Factory $factory
 */
$factory->define(Setting::class, function (Generator $faker) {
    return [
        "key" => $faker->uuid,
        "value" => $faker->sha1,
        "visible" => $faker->boolean,
    ];
});
