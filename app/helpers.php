<?php

declare(strict_types=1);

use App\Models\User;
use Braintree\Gateway;
use Faker\Factory as FakerFactory;

/**
 * Get two coherent timestamps.
 *
 * @param string $start
 * @param int    $bias
 *
 * @return array
 */
function timestamps(string $start = '-1 month', int $bias = 90): array
{
    $format = 'Y-m-d H:i:s';
    $faker  = FakerFactory::create();

    $createdAt = $faker->dateTimeBetween($start);
    $updatedAt = $faker->boolean($bias) ? $createdAt : $faker->dateTimeBetween($createdAt);

    return [$createdAt->format($format), $updatedAt->format($format)];
}

/**
 * Check if a user has the given scope.
 *
 * @param User|null $user
 * @param string    $scope The scope to check
 *
 * @return bool
 */
function scope(?User $user, string $scope): bool
{
    return $user instanceof User && $user->hasScope($scope);
}

function gateway(): Gateway
{
    return app(Gateway::class);
}
