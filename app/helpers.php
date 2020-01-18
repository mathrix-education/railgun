<?php

declare(strict_types=1);

use App\Models\User;
use Braintree\Gateway;
use Faker\Factory as FakerFactory;
use Illuminate\Support\Facades\Auth;

if (!function_exists('timestamps')) {
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
}

if (!function_exists('hasScope')) {
    /**
     * Check if a user has the given scope.
     *
     * @param User|null $user
     * @param string    $scope The scope to check
     *
     * @return bool
     */
    function hasScope(?User $user, string $scope): bool
    {
        return $user instanceof User && $user->hasScope($scope);
    }
}

if (!function_exists('scope')) {
    /**
     * Check if the current logged user has the given scope.
     *
     * @param string $scope The scope to check
     *
     * @return bool
     */
    function scope(string $scope): bool
    {
        /** @var User $user */
        $user = Auth::user();

        return hasScope($user, $scope);
    }
}

if (!function_exists('gateway')) {
    function gateway(): Gateway
    {
        return app(Gateway::class);
    }
}
