<?php

declare(strict_types=1);

use App\Models\Discount;
use Faker\Generator;
use Illuminate\Database\Eloquent\Factory;

/**
 * DiscountFactory.
 *
 * @var Factory $factory
 */
$factory->define(Discount::class, static function (Generator $faker) {
    $type  = $faker->randomElement(Discount::TYPES);
    $value = 0;

    switch ($type) {
        case 'absolute':
            $value = random_int(3, 10);
            break;
        case 'percentage':
            $value = random_int(1, 3) * 10;
            break;
    }

    return [
        'code' => mb_substr($faker->md5, 0, 20),
        'type' => $type,
        'value' => $value,
        'counter' => $faker->boolean ? ($faker->boolean ? 0 : random_int(10, 20)) : -1,
    ];
});
