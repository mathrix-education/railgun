<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Class SitemapAll
 *
 * @author Mathrix Back-End Team
 * @package App\Console\Commands
 * @since 1.0.1
 * @copyright Mathrix
 */
class SitemapAll extends Command
{
    protected $signature = "sitemap:all";


    public function handle()
    {
        $this->call("sitemap:videos");
        $this->call("sitemap:content");
    }
}