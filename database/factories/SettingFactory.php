<?php

declare(strict_types=1);

use Faker\Generator;
use Illuminate\Database\Eloquent\Factory;
use Railgun\Models\Setting;

/**
 * @var Factory $factory
 */
$factory->define(Setting::class, static function (Generator $faker) {
    return [
        'key'     => md5(time() . $faker->randomNumber()),
        'value'   => md5(md5(time() . $faker->randomNumber())),
        'visible' => $faker->boolean(90),
    ];
});
