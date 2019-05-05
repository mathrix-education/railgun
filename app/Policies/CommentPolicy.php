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
        $hasScope = scope($currentUser, "comments:read");
        $ownsComment = $currentUser->id === $comment->user_id;
        $isTooLate = $comment->updated_at->addDays(3)->isPast();

        return $hasScope || ($ownsComment && !$isTooLate);
    }
}
