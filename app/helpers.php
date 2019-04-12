<?php

use Faker\Factory as FakerFactory;

if (!function_exists("config_path")) {
    /**
     * Get the configuration path.
     *
     * @param string $path
     *
     * @return string
     */
    function config_path($path = "")
    {
        return app()->basePath() . "/config" . ($path ? "/" . $path : $path);
    }
}

if (!function_exists("public_path")) {
    /**
     * Get the public path.
     *
     * @param string $path
     *
     * @return string
     */
    function public_path($path = "")
    {
        return app()->basePath() . "/public" . ($path ? "/" . $path : $path);
    }
}

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
