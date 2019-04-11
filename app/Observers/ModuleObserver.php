<?php

namespace App\Observers;

use App\Exceptions\ForeignConstraintsException;
use App\Models\Comment;
use App\Models\Module;
use App\Models\Report;

/**
 * Class ModuleObserver.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 4.3.0
 */
class ModuleObserver extends BaseObserver
{
    /**
     * Detach all activities linked with the deleting module.
     *
     * @param Module $module The Module which will be deleted.
     *
     * @return bool
     * @throws ForeignConstraintsException
     */
    public function deleting(Module $module)
    {
        $this->handleRelationDeletion($module, "posts");
        $this->handleRelationDeletion($module, "products");

        Comment::withTrashed()
            ->where("module_id", "=", $module->id)
            ->get()
            ->each(function (Comment $comment) {
                $comment->forceDelete();
            });
        $module->reports()
            ->get()
            ->each(function(Report $report) {
                $report->delete();
            });


        return true;
    }
}
