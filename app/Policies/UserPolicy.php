<?php
namespace App\Policies;

use App\Models\User;

/**
 * Class UserPolicy
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @package App\Policies
 * @since 1.0.0
 * @copyright Mathrix Education SA
 */
class UserPolicy extends BasePolicy
{

    /**
     * @param User $currentUser
     * @param User $user
     *
     * @return bool
     */
    public function get(User $currentUser, User $user)
    {
        return $currentUser->id === $user->id || $this->atLeastModerator($currentUser) && $user->type == 'registered';
    }


    /**
     * @param User $currentUser
     * @param User $user
     *
     * @return bool
     */
    public function edit(User $currentUser, User $user)
    {
        return $currentUser->id === $user->id;
    }


    /**
     * @param User $currentUser
     * @param User $user
     *
     * @return bool
     */
    public function points(User $currentUser, User $user)
    {
        return $currentUser->id === $user->id;
    }


    /**
     * @param User $currentUser
     * @param User $user
     *
     * @return bool
     */
    public function delete(User $currentUser, User $user)
    {
        return $currentUser->id === $user->id;
    }
}
