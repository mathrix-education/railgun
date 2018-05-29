<?php

use App\Models\Purchase;
use App\Models\Section;
use App\Models\Training;
use App\Models\User;
use App\Utils\StripeUtils;
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
    $purchasable = $modelClass::whereVisible(true)->inRandomOrder()->firstOrFail();

    switch ($purchasableType) {
        case "training":
            $price = $purchasable->price;
            break;
        case "section":
            $price = StripeUtils::SECTION_PRICE;
            break;
        default:
            $price = random_int(5, 50) * 100;
    }

    $refund = 0;

    if ($purchasableType == "training" && $faker->boolean) {
        $refund = $faker->boolean ? random_int(1, ($price - 3) / 100) : $price;
    }

    return [
        "stripe_id" => "ch_" . substr($faker->sha1, 0, 25),
        "price" => $price,
        "refund" => $refund,
        "user_id" => User::random()->id,
        "purchasable_id" => $purchasable->id,
        "purchasable_type" => $purchasableType,
        "created_at" => $created,
        "updated_at" => $faker->boolean(10) ? $faker->dateTimeBetween($created, "now") : $created,
    ];
});
