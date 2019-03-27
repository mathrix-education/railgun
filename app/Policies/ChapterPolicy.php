<?php

namespace App\Policies;

use App\Models\User;

/**
 * Class ChapterPolicy.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     1.0.0
 * @copyright Mathrix Education SA
 * @package   App\Policies
 */
class ChapterPolicy extends BasePolicy
{
    /**
     * @param User $currentUser
     *
     * @return bool
     */
    public function index(User $currentUser)
    {
        return $currentUser->isModerator();
    }


    /**
     * @return bool
     */
    public function get()
    {
        return true;
    }


    /**
     * @param User $currentUser
     *
     * @return bool
     */
    public function post(User $currentUser)
    {
        return $currentUser->isModerator();
    }


    /**
     * @param User $currentUser
     *
     * @return bool
     */
    public function patch(User $currentUser)
    {
        return $currentUser->isModerator();
    }


    /**
     * @param User $currentUser
     *
     * @return bool
     */
    public function delete(User $currentUser)
    {
        return $currentUser->isModerator();
    }
}
