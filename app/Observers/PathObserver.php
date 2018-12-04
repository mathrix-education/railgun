<?php
namespace App\Observers;

use App\Exceptions\Http422ModelSaveException;
use App\Models\Comment;
use App\Models\Mark;
use App\Models\Path;

/**
 * Class PathObserver.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @since
 * @copyright Mathrix Education SA
 */
class PathObserver extends BaseObserver
{
    /**
     * Detach all activities link with the deleting notion.
     *
     * @param Path $path
     *
     * @return bool
     * @throws Http422ModelSaveException
     */
    public function deleting($path)
    {
        Comment::withTrashed()
            ->where("path_id", "=", $path->id)
            ->forceDelete();
        Mark::where("path_id", "=", $path->id)
            ->delete();

        return true;
    }
}
