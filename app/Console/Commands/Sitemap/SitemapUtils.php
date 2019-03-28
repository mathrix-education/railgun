<?php

namespace App\Console\Commands\Sitemap;

use Illuminate\Support\Facades\Storage;
use Laravelium\Sitemap\Sitemap;

/**
 * Trait SitemapUtils.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 4.3.0
 */
trait SitemapUtils
{
    protected $defaultSitemapItem = [
        "url" => null,
        "lastmod" => null,
        "priority" => null,
        "freq" => null,
        "images" => null,
        "title" => null,
        "translations" => null,
        "videos" => null
    ];


    /**
     * Get the webrooted url
     *
     * @param string $url
     *
     * @return string
     */
    public function url(string $url)
    {
        return env("APP_WEBROOT") . "$url";
    }


    /**
     * Save the sitemap
     *
     * @param array $sitemapArray
     * @param string $sitemapName
     * @param \Closure $callback
     */
    public function saveSitemap(array $sitemapArray, string $sitemapName = "sitemap", \Closure $callback = null)
    {
        /** @var Sitemap $sitemap */
        $sitemap = app()->make("sitemap");

        foreach ($sitemapArray as $sitemapItem) {
            $sitemapItem = array_merge($this->defaultSitemapItem, $sitemapItem);

            $sitemap->add(
                $sitemapItem["url"],
                $sitemapItem["lastmod"],
                $sitemapItem["priority"],
                $sitemapItem["freq"],
                $sitemapItem["images"],
                $sitemapItem["title"],
                $sitemapItem["translations"],
                $sitemapItem["videos"]
            );
        }

        if (!in_array("sitemaps", Storage::disk("storage")->directories())) {
            Storage::disk("storage")->makeDirectory("sitemaps");
        }

        if ($callback !== null) {
            $sitemap = $callback($sitemap);
        }

        $sitemap->store("xml", $sitemapName, storage_path("sitemaps"));

        // Generate symlink if not in local environment
        if (env("APP_ENV") != "local") {
            $existing = "/var/www/" . env("APP_ENV") . "/api/current/storage/sitemaps/$sitemapName.xml";
            $symbolic = "/var/www/" . env("APP_ENV") . "/client/current/dist/browser/$sitemapName.xml";

            exec("ln -s -f $existing $symbolic");
        }
    }
}
