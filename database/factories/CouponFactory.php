<?php

declare(strict_types=1);

use Faker\Generator;
use Illuminate\Database\Eloquent\Factory;
use Railgun\Models\Coupon;
use Railgun\Support\Timestamps;

/**
 * @var Factory $factory
 */
$factory->define(Coupon::class, static function (Generator $faker) {
    if ($faker->boolean) {
        $type  = 'percentage';
        $value = $faker->randomDigit * 10;
    } else {
        $type  = 'absolute';
        $value = $faker->randomDigit * 100;
    }
    $timestamps = Timestamps::factory();

    return [
        'id'         => mb_strtoupper($faker->domainWord . $faker->randomNumber(4)),
        'type'       => $type,
        'value'      => $value,
        'counter'    => $faker->boolean ? -1 : $faker->randomDigit,
        'expires_at' => $faker->dateTimeBetween($timestamps->createdAt),
        'created_at' => $timestamps->createdAt,
        'updated_at' => $timestamps->updatedAt,
    ];
});
