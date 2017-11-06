<?php
namespace App\Policies;

use App\Models\Comment;
use App\Models\User;
use Carbon\Carbon;

/**
 * Class CommentPolicy
 *
 * @author Mathrix Back-End Team
 * @package App\Policies
 * @since 0.8.1
 * @copyright Mathrix
 */
class CommentPolicy extends BasePolicy
{
    /**
     * @return bool
     */
    public function add()
    {
        return true;
    }


    /**
     * @param User $currentUser
     * @param Comment $comment
     *
     * @return bool
     */
    public function edit(User $currentUser, Comment $comment)
    {
        if ($this->atLeastModerator($currentUser)) {
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
        return $this->atLeastModerator($currentUser);
    }
}