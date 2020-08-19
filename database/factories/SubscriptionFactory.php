<?php

declare(strict_types=1);

use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Carbon;
use Railgun\Billing\Stripe\Models\ModeMachine;
use Railgun\Models\Subscription;
use Stripe\Subscription as StripeSubscription;

/**
 * @var Factory $factory
 */
$factory->define(Subscription::class, static function (Faker $faker) {
    $ref = Carbon::now()->subDays(15);

    return [
        'id'                   => $faker->regexify('sub_[a-z0-9]{14}'),
        'plan'                 => $faker->randomElement(ModeMachine::PLANS),
        'customer_id'          => $faker->regexify('cus_[a-z0-9]{14}'),
        'status'               => StripeSubscription::STATUS_ACTIVE,
        'current_period_start' => $ref,
        'current_period_end'   => $ref->clone()->addMonth(),
        'created_at'           => $ref,
        'updated_at'           => $ref,
    ];
});
