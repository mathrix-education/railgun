<?php

use App\Models\Section;
use App\Models\Setting;
use App\Models\Training;
use Faker\Generator;

/**
 * SettingFactory
 *
 * @author Mathrix Team
 * @var Illuminate\Database\Eloquent\Factory $factory
 */
$factory->define(Setting::class, function (Generator $faker) {
    return [
        "key" => $faker->uuid,
        "value" => $faker->sha1,
        "visible" => $faker->boolean
    ];
});
