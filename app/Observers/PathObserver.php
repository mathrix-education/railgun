<?php
namespace App\Observers;

use App\Models\Comment;
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
     */
    public function deleting($path)
    {
        Comment::where("path_id", "=", $path->id)
            ->forceDelete();

        return true;
    }
}
