<?php

namespace App\Observers;

use App\Models\Comment;
use App\Models\Module;
use App\Models\Post;
use App\Models\Product;
use App\Models\Reference;
use App\Models\Report;

/**
 * Class ModuleObserver.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     4.3.0
 * @copyright Mathrix Education SA
 * @package   App\Observers
 */
class ModuleObserver extends BaseObserver
{
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
        Reference::where("module_id", "=", $module->id)
            ->get()
            ->each(function (Reference $reference) {
                $reference->delete();
            });
        Report::where("module_id", "=", $module->id)
            ->get()
            ->each(function (Report $report) {
                $report->delete();
            });

        return true;
    }
}
