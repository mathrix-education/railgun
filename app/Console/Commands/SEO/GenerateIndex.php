<?php
namespace App\Console\Commands\SEO;

use App\Models\Class_;

/**
 * Class GenerateIndex.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @since     4.1.1
 * @copyright Mathrix Education SA
 */
class GenerateIndex
{
    protected $signature = "seo:generate:index";

    public function handle()
    {
        $classes = Class_::whereIn("slug", ["3eme", "2nde", "1ere-s", "terminale-s"]);
    }
}
