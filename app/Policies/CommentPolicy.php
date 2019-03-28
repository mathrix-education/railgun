<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;
use Carbon\Carbon;

/**
 * Class CommentPolicy.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 1.0.0
 */
class CommentPolicy extends BasePolicy
{
    /**
     * @param User $currentUser
     * @param Comment $comment
     *
     * @return bool
     */
    public function patch(User $currentUser, Comment $comment)
    {
        if ($currentUser->tokenCan("moderator")) {
            return true;
        }

        if (
            $currentUser->id === $comment->user_id
            && $comment->updated_at > Carbon::now()->subDays(3)
        ) {
            return true;
        }

        return false;
    }
}
