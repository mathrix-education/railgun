<?php

namespace App\Policies;

use App\Models\User;

/**
 * Class ActivityPolicy
 *
 * @author Mathrix Team
 * @package App\Policies
 */
class ActivityPolicy extends BasePolicy
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
     * @param User $currentUser
     *
     * @return bool
     */
    public function add(User $currentUser)
    {
        return $this->atLeastModerator($currentUser);
    }


    /**
     * @param User $currentUser
     *
     * @return bool
     */
    public function edit(User $currentUser)
    {
        return $this->atLeastModerator($currentUser);
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