<?php
namespace App\Console\Commands;

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
     */
    public function saveSitemap(array $sitemapArray, string $name = "sitemap")
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

        $sitemap->store("xml", $name, storage_path("sitemaps"));
    }
}