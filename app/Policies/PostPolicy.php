<?php
namespace App\Policies;

use App\Models\User;

/**
 * Class PostPolicy
 *
 * @author Mathrix Team
 * @package App\Policies
 */
class PostPolicy extends BasePolicy
{
    /**
     * @param User $currentUser
     *
     * @return bool
     */
    public function add(User $currentUser)
    {
        if ($currentUser->tokenCan('moderator')) {
            return true;
        }

        return false;
    }

    /**
     * @param User $currentUser
     *
     * @return bool
     */
    public function edit(User $currentUser)
    {
        if ($currentUser->tokenCan('moderator')) {
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