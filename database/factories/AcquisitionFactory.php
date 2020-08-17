<?php

declare(strict_types=1);

use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use Railgun\Models\Acquisition;
use Railgun\Models\Chapter;
use Railgun\Models\Contracts\Sellable;
use Railgun\Models\Pack;
use Railgun\Models\Training;
use Railgun\Models\User;
use Railgun\Support\Timestamps;

/**
 * @var Factory $factory
 */
$factory->define(Acquisition::class, static function (Faker $faker) {
    /** @var Sellable|string $productClass */
    $productClass = $faker->randomElement([Chapter::class, Pack::class, Training::class]);
    $product      = $productClass::random();
    $timestamps   = Timestamps::factory();

    return [
        'id'             => $faker->uuid,
        'user_id'        => User::random()->id,
        'product_type'   => resolveMorph($product),
        'product_id'     => $product->getKey(),
        'transaction_id' => $faker->regexify('in_[a-zA-Z0-9]{14}'),
        'created_at'     => $timestamps->createdAt,
        'updated_at'     => $timestamps->updatedAt,
    ];
});
