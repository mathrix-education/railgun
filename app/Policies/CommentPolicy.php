<?php
namespace App\Policies;

use App\Models\Comment;
use App\Models\User;
use Carbon\Carbon;

/**
 * Class CommentPolicy
 *
 * @author Mathrix Team
 * @package App\Policies
 */
class CommentPolicy extends BasePolicy
{
    /**
     * @param User $currentUser
     * @param Comment $comment
     *
     * @return bool
     */
    public function edit(User $currentUser, Comment $comment)
    {
        if ($currentUser->tokenCan('moderator')) {
            return true;
        }

        if ($currentUser->id == $comment->user_id && $comment->updated_at > Carbon::now()->subDays(3)) {
            return true;
        }

        return false;
    }


    /**
     * @param User $currentUser
     *
     * @return bool
     */
    public function delete(User $currentUser)
    {
        if ($currentUser->tokenCan('moderator')) {
            return true;
        }

        return false;
    }
}