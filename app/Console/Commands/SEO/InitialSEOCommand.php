<?php

namespace App\Console\Commands\SEO;

use App\Models\Path;
use Illuminate\Console\Command;

/**
 * Class InitialSEOCommand.
 *
 * @author    Mathieu Bour <mathieu@mathrix.fr>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     4.3.0
 * @copyright Mathrix Education SA
 * @package   App\Console\Commands\SEO
 */
class InitialSEOCommand extends Command
{
    protected $signature = "seo:patch";


    public function handle()
    {
        $paths = Path::where("seo_index", "=", true)
            ->whereNotNull("module_id")
            ->get();

        foreach ($paths as $path) {
            $patchedPath = Path::retrieve([
                "country_id" => $path->country_id,
                "class_id" => $path->class_id,
                "discipline_id" => $path->discipline_id,
                "chapter_id" => $path->chapter_id,
                "notion_id" => $path->notion_id,
                "activity_id" => $path->activity_id,
                "module_id" => null
            ]);

            $patchedPath->seo_index = true;
            $patchedPath->save();

            $path->seo_index = false;
            $path->save();
        }
    }
}
