<?php
namespace App\Observers;

use App\Models\Activity;
use App\Models\Comment;
use App\Models\Module;
use App\Models\Path;
use App\Models\Post;
use App\Models\Product;
use App\Models\Report;

/**
 * Class ModuleObserver.
 *
 * @author    Mathrix Back-End Team
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     4.3.0
 * @copyright Mathrix Education SA
 * @package   App\Observers
 */
class ModuleObserver extends BaseObserver
{
    /**
     * @param Module $module
     *
     * @return bool
     */
    public function saving($module)
    {
        // Sync activities
        if (isset($module->link["activities"])) {
            $toSync = [];
            foreach ($module->link["activities"] as $order => $notionId) {
                if (Activity::whereId($notionId)
                    ->exists()) {
                    $toSync[$notionId] = ["order" => $order];
                }
            }

            $module->activities()
                ->sync($toSync);
        }

        if (isset($module->link)) {
            unset($module->link);
        }

        return true;
    }

    /**
     * Detach all activities linked with the deleting module.
     *
     * @param Module $module
     *
     * @throws \App\Exceptions\Http422ModelSaveException
     *
     * @return bool
     */
    public function deleting($module)
    {
        $module->activities()
            ->detach();

        Comment::withTrashed()
            ->where("module_id", "=", $module->id)
            ->get()
            ->each(function (Comment $comment) {
                $comment->forceDelete();
            });
        Post::where("module_id", "=", $module->id)
            ->get()
            ->each(function (Post $post) {
                $post->delete();
            });
        Product::where("module_id", "=", $module->id)
            ->get()
            ->each(function (Product $product) {
                $product->delete();
            });
        Report::where("module_id", "=", $module->id)
            ->get()
            ->each(function (Report $report) {
                $report->delete();
            });
        Path::where("module_id", "=", $module->id)
            ->get()
            ->each(function (Path $path) {
                $path->delete();
            });

        return true;
    }
}
