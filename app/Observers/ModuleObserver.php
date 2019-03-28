<?php

namespace App\Observers;

use App\Models\Comment;
use App\Models\Module;

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
     * @throws \App\Exceptions\ForeignConstraintsException
     */
    public function deleting(Module $module)
    {
        Comment::withTrashed()
            ->where("module_id", "=", $module->id)
            ->get()
            ->each(function (Comment $comment) {
                $comment->forceDelete();
            });

        $this->handleRelationDeletion($module, "posts");
        $this->handleRelationDeletion($module, "products");
        $this->handleRelationDeletion($module, "references");
        $this->handleRelationDeletion($module, "reports");

        return true;
    }
}
