<?php

use App\Models\User;
use Faker\Factory as FakerFactory;
use Illuminate\Database\Eloquent\Relations\Relation;
use Ramsey\Uuid\Uuid;

//if (!function_exists("config_path")) {
//    /**
//     * Get the configuration path.
//     *
//     * @param string $path
//     *
//     * @return string
//     */
//    function config_path($path = "")
//    {
//        return app()->basePath() . "/config" . ($path ? "/" . $path : $path);
//    }
//}
//
//if (!function_exists("public_path")) {
//    /**
//     * Get the public path.
//     *
//     * @param string $path
//     *
//     * @return string
//     */
//    function public_path($path = "")
//    {
//        return app()->basePath() . "/public" . ($path ? "/" . $path : $path);
//    }
//}

if (!function_exists("timestamps")) {
    /**
     * Get two coherent timestamps.
     *
     * @param string $start
     * @param int $bias
     *
     * @return array
     */
    function timestamps($start = "-1 month", int $bias = 90): array
    {
        $format = "Y-m-d H:i:s";
        $faker = FakerFactory::create();

        $createdAt = $faker->dateTimeBetween($start);
        $updatedAt = $faker->boolean($bias) ? $createdAt : $faker->dateTimeBetween($createdAt);

        return [$createdAt->format($format), $updatedAt->format($format)];
    }
}

if (!function_exists("scope")) {
    /**
     * Check if a user has the given scope.
     *
     * @param User|null $user
     * @param string $scope The scope to check
     *
     * @return bool
     */
    function scope($user, string $scope): bool
    {
        return $user instanceof User && $user->hasScope($scope);
    }
}

if (!function_exists("gateway")) {
    /**
     * @return \Braintree\Gateway
     */
    function gateway(): \Braintree\Gateway
    {
        return app(\Braintree\Gateway::class);
    }
}


if (!function_exists("uuid")) {
    function uuid(): ?string
    {
        try {
            return Uuid::uuid4();
        } catch (Exception $e) {
            return null;
        }
    }
}

if ((!function_exists("morph_alias"))) {
    function morph_alias($class)
    {
        return array_search($class, Relation::morphMap()) ?: $class;
    }
}
