<?php

declare(strict_types=1);

namespace App\Observers;

use App\Exceptions\ForeignConstraints;
use App\Models\Comment;
use App\Models\Module;
use App\Models\Report;

class ModuleObserver extends BaseObserver
{
    /**
     * After save.
     * Update parent notion's activity counter.
     */
    public function saved(Module $module) : bool
    {
        if ($module->wasChanged(['activity_id', 'visible'])) {
            $module->activity->updateCounters();
            $module->unsetRelation('activity');
        }

        return true;
    }

    /**
     * Detach all activities linked with the deleting module.
     *
     * @param Module $module The Module which will be deleted.
     *
     * @throws ForeignConstraints
     */
    public function deleting(Module $module) : bool
    {
        $this->handleRelationDeletion($module, 'posts');
        $this->handleRelationDeletion($module, 'products');

        Comment::withTrashed()
            ->where('module_id', '=', $module->id)
            ->get()
            ->each(static function (Comment $comment) : void {
                $comment->forceDelete();
            });
        $module->reports()
            ->get()
            ->each(static function (Report $report) : void {
                $report->delete();
            });

        return true;
    }
}
