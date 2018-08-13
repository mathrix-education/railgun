<?php

if (!function_exists("config_path")) {
    /**
     * Get the configuration path.
     *
     * @param  string $path
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
     * @param  string $path
     *
     * @return string
     */
    function public_path($path = "")
    {
        return app()->basePath() . "/public" . ($path ? "/" . $path : $path);
    }
}

if (!function_exists('glob_recursive')) {
    // Does not support flag GLOB_BRACE
    function glob_recursive($pattern, $flags = 0)
    {
        $files = glob($pattern, $flags);
        foreach (glob(dirname($pattern) . '/*', GLOB_ONLYDIR | GLOB_NOSORT) as $dir) {
            $files = array_merge($files, glob_recursive($dir . '/' . basename($pattern), $flags));
        }
        return $files;
    }
}

if (!function_exists('build_date')) {
    /**
     * Get the build date
     *
     * @return string
     */
    function build_date()
    {
        $build = basename(dirname(__DIR__, 1));

        try {
            $build = \Carbon\Carbon::createFromFormat("Y-m-d_H-i-s", $build)->toIso8601String();
        } catch (InvalidArgumentException $e) {
        }

        return $build;
    }
}
