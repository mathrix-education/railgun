<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;
use Carbon\Carbon;

/**
 * Class CommentPolicy
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @package App\Policies
 * @since 1.0.0
 * @copyright Mathrix Education SA
 */
class CommentPolicy extends BasePolicy
{
    /**
     * @param User $currentUser
     *
     * @return bool
     */
    public function index(User $currentUser)
    {
        return $this->atLeastModerator($currentUser);
    }


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


    /**
     * @return bool
     */
    public function byNotion()
    {
        return true;
    }


    /**
     * @return bool
     */
    public function byPost()
    {
        return true;
    }


    /**
     * @param User $currentUser
     *
     * @return bool
     */
    public function search(User $currentUser)
    {
        return $this->atLeastModerator($currentUser);
    }


    /**
     * @param User $currentUser
     *
     * @return bool
     */
    public function latest(User $currentUser)
    {
        return $this->atLeastModerator($currentUser);
    }
}
