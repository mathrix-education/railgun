<?php

use App\Models\FamilyPack;
use App\Models\User;
use Faker\Generator;

/**
 * FamilyPackFactory
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @since     4.1.1
 * @copyright Mathrix Education SA
 *
 * @var Illuminate\Database\Eloquent\Factory $factory
 */
$factory->define(FamilyPack::class, function (Generator $faker) {
    $parent = User::inRandomOrder()
        ->doesntHave("familyPackAsParent")
        ->firstOrFail();
    $createdAt = $faker->dateTimeBetween("-1 month")->format("Y-m-d H:i:s");

    return [
        "braintree_id" => "sub_mock_valid",
        "children_count" => random_int(1, 5),
        "parent_id" => $parent->id,
        "created_at" => $createdAt,
        "updated_at" => $faker->boolean ? $createdAt : $faker->dateTimeBetween($createdAt)->format("Y-m-d H:i:s")
    ];
});
