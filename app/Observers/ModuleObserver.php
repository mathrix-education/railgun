<?php

namespace App\Observers;

use App\Models\Comment;
use App\Models\Module;
use App\Models\Path;
use App\Models\Post;
use App\Models\Product;
use App\Models\Reference;
use App\Models\Report;
use Illuminate\Support\Arr;

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
     * @param Module $module
     *
     * @return bool
     */
    public function saving($module)
    {
        // Disabled event dispatcher to avoid infinite loops
        $dispatcher = Module::getEventDispatcher();
        Module::unsetEventDispatcher();

        // Propagate modification to cloned modules
        $clonedData = Arr::only($module->getOriginal(), Module::CLONE_FIELDS);
        $module->clones()->get()->map(function (Module $clone) use ($clonedData) {
            $clone->update($clonedData);
        });

        // Enable event dispatcher
        Module::setEventDispatcher($dispatcher);

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
