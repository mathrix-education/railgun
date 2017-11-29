<?php
namespace App\Console\Commands;

use Illuminate\Support\Facades\Storage;
use Roumen\Sitemap\Sitemap;

/**
 * Trait SitemapUtils
 *
 * @package App\Console\Commands
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
     * @param string $name
     * @param \Closure $callback
     */
    public function saveSitemap(array $sitemapArray, string $name = "sitemap", \Closure $callback = null)
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
            $callback($sitemap);
        }

        $sitemap->store("xml", $name, storage_path("sitemaps"));

        // Generate symlink if not in local environment
        if (env("APP_ENV") != "local") {
            $sitemapFiles = Storage::disk("storage")->files("sitemaps");

            foreach ($sitemapFiles as $sitemapFile) {
                symlink(
                    "/var/www/" . env("APP_ENV") . "/api/current/storage/$sitemapFile",
                    "/var/www/" . env("APP_ENV") . "/client/current/dist/browser/$sitemapFile"
                );
            }
        }
    }
}