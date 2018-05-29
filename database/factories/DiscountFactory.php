<?php

use App\Models\Discount;
use Faker\Generator;

/**
 * DisciplineFactory
 *
 * @author Mathrix Team
 * @var Illuminate\Database\Eloquent\Factory $factory
 */
$factory->define(Discount::class, function (Generator $faker) {
    $type = $faker->randomElement(Discount::TYPES);
    $value = 0;

    switch ($type) {
        case "absolute":
            $value = random_int(3, 10) * 100;
            break;
        case "percentage":
            $value = random_int(1, 3) * 10;
            break;
    }

    return [
        "code" => substr($faker->md5, 0, 20),
        "type" => $type,
        "value" => $value,
        "counter" => $faker->boolean ? ($faker->boolean ? 0 : random_int(10, 20)) : -1,
    ];
});
