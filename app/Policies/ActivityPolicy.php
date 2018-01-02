<?php
namespace App\Policies;

use App\Models\User;

/**
 * Class ActivityPolicy
 *
 * @author Mathrix Back-End Team
 * @package App\Policies
 * @since 1.0.0
 * @copyright Mathrix SA
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
