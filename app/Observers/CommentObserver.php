<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Comment;
use App\Models\Vote;
use Exception;

class CommentObserver extends BaseObserver
{
    /**
     * @param Comment $comment
     *
     * @return bool
     *
     * @throws Exception
     */
    public function deleting(Comment $comment): bool
    {
        $children = Comment::withTrashed()
            ->where('parent_id', '=', $comment->id)
            ->get();

        if ($comment->isForceDeleting()) {
            $comment->votes()->each(static function (Vote $vote): void {
                $vote->delete();
            });
        }

        /** @var Comment $child */
        foreach ($children as $child) {
            if ($comment->isForceDeleting()) {
                $child->forceDelete();
            } else {
                $child->delete();
            }
        }

        return true;
    }
}
