<?php

declare(strict_types=1);

namespace App\Services\Sitemap;

use function env;
use function ltrim;
use function rtrim;

/**
 * Set of utils related to Sitemap generation.
 */
class SitemapUtils
{
    /**
     * Using the ANGULAR_URL environment variable, generate the proper front-end url.
     *
     * @param string $loc The internal generated url.
     *
     * @return string
     */
    public static function loc(string $loc): string
    {
        return rtrim(env('ANGULAR_URL'), '/') . '/' . ltrim($loc, '/');
    }
}
