<?php

use App\Models\Category;
use App\Models\Module;
use App\Models\Post;
use App\Models\Purchase;
use App\Models\Section;
use App\Models\Training;
use App\Models\User;
use Faker\Generator;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * PurchaseFactory
 *
 * @author Mathrix Team
 * @var Illuminate\Database\Eloquent\Factory $factory
 */
$factory->define(Purchase::class, function (Generator $faker) {
    $created = $faker->dateTimeBetween("-30 days", "now");
    /** @var Training|Section $modelClass */
    $purchasableType = $faker->randomElement(["training", "section"]);
    /** @var Training|Section $purchasable */
    $modelClass = Relation::getMorphedModel($purchasableType);
    $purchasableId = $modelClass::random()->id;

    return [
        "uuid" => $faker->uuid,
        "price" => random_int(5, 10) * 100,
        "user_id" => User::random()->id,
        "purchasable_id" => $purchasableId,
        "purchasable_type" => $purchasableType,
        "created_at" => $created,
        "updated_at" => $faker->boolean(10) ? $faker->dateTimeBetween($created, "now") : $created,
    ];
});
