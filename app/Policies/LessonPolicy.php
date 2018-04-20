<?php
namespace App\Policies;

use App\Models\User;

/**
 * Class LessonPolicy
 *
 * @author Mathrix Back-End Team
 * @package App\Policies
 * @since 1.0.1
 * @copyright Mathrix Education SA
 */
class LessonPolicy extends BasePolicy
{
    /**
     * @param User $user
     *
     * @return bool
     */
    public function index(User $user)
    {
        return $this->atLeastModerator($user);
    }


    /**
     * @param User $user
     *
     * @return bool
     */
    public function get(User $user)
    {
        return $this->atLeastModerator($user);
    }


    /**
     * @param User $user
     *
     * @return bool
     */
    public function add(User $user)
    {
        return $this->atLeastModerator($user);
    }


    /**
     * @param User $user
     *
     * @return bool
     */
    public function edit(User $user)
    {
        return $this->atLeastModerator($user);
    }


    /**
     * @param User $user
     *
     * @return bool
     */
    public function delete(User $user)
    {
        return $this->atLeastModerator($user);
    }
}
