<?php

use App\Models\User;
use App\Utils\StripeUtils;
use Laravel\Cashier\Subscription;
use Faker\Generator;

/**
 * SubscriptionFactory
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @var Illuminate\Database\Eloquent\Factory $factory
 */
$factory->define(Subscription::class, function (Generator $faker) {
    $user = User::doesntHave("subscriptions")
        ->inRandomOrder()
        ->firstOrFail();
    $plan = $faker->randomElement(array_keys(StripeUtils::ALLOWED_STRIPE_PLANS));
    $trialEndsAt = in_array($plan, ["v3_biannually", "v3_annually"]) ? StripeUtils::getStripePlanEndDate($plan) :
        null;
    $createdAt = $faker->dateTimeBetween("-1 years", "now");

    return [
        "user_id" => $user->id,
        "name" => "premium",
        "stripe_id" => "sub_" . substr($faker->md5, 0, 14),
        "stripe_plan" => $plan,
        "quantity" => 1,
        "trial_ends_at" => $trialEndsAt,
        "created_at" => $createdAt,
        "updated_at" => $createdAt
    ];
});



/**
 * SubscriptionFactory (cancelled)
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @var Illuminate\Database\Eloquent\Factory $factory
 */
$factory->defineAs(Subscription::class, "canceled", function (Generator $faker) use ($factory) {
    $data = $factory->raw(Subscription::class);

    $endsAt = $faker->dateTimeBetween($data["created_at"], "now");

    $data["ends_at"] = $endsAt;
    $data["updated_at"] = $endsAt;

    return $data;
});

