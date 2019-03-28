<?php

namespace App\Console\Commands\Sitemap;

use Illuminate\Console\Command;
use Laravelium\Sitemap\Sitemap;

/**
 * Class SitemapAll.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 1.0.1
 */
class SitemapAll extends Command
{
    use SitemapUtils;

    protected $signature = "sitemap:all";


    public function handle()
    {
        $this->call("sitemap:content");

        /** @var Sitemap $sitemap */
        $sitemap = app()->make("sitemap");

        $sitemap->addSitemap($this->url("/sitemap.main.xml"));
        $sitemap->addSitemap($this->url("/sitemap.modules.xml"));

        $sitemap->store("sitemapindex", "sitemap", storage_path("sitemaps"));

        if (env("APP_ENV") != "local") {
            $existing = "/var/www/" . env("APP_ENV") . "/api/current/storage/sitemaps/sitemap.xml";
            $symbolic = "/var/www/" . env("APP_ENV") . "/client/current/dist/browser/sitemap.xml";

            if (file_exists($symbolic)) {
                unlink($symbolic);
            }

            exec("ln -s $existing $symbolic");
        }
    }
}
