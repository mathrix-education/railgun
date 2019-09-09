<?php

declare(strict_types=1);

use App\Models\Setting;
use Faker\Generator;
use Illuminate\Database\Eloquent\Factory;

/**
 * SettingFactory.
 *
 * @var Factory $factory
 */
$factory->define(Setting::class, static function (Generator $faker) {
    return [
        'key' => $faker->uuid,
        'value' => $faker->sha1,
        'visible' => $faker->boolean,
    ];
});
