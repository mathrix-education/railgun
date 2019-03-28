<?php

namespace App\Policies;

use App\Models\User;

/**
 * Class FilePolicy.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 4.3.0
 */
class FilePolicy extends BasePolicy
{
    /**
     * @param User $currentUser
     *
     * @return bool
     */
    public function info(User $currentUser)
    {
        return $currentUser->isModerator();
    }


    /**
     * @param User $currentUser
     *
     * @return bool
     */
    public function explore(User $currentUser)
    {
        return $currentUser->isModerator();
    }


    /**
     * @param User $currentUser
     *
     * @return bool
     */
    public function upload(User $currentUser)
    {
        return $currentUser->isModerator();
    }


    /**
     * @param User $currentUser
     *
     * @return bool
     */
    public function forceUpload(User $currentUser)
    {
        return $currentUser->isAdmin();
    }


    /**
     * @param User $currentUser
     *
     * @return bool
     */
    public function rename(User $currentUser)
    {
        return $currentUser->isModerator();
    }


    /**
     * @param User $currentUser
     *
     * @return bool
     */
    public function forceRename(User $currentUser)
    {
        return $currentUser->isAdmin();
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


    /**
     * @param User $currentUser
     *
     * @return bool
     */
    public function forceDelete(User $currentUser)
    {
        return $currentUser->isAdmin();
    }
}
