<?php
namespace App\Policies;

use App\Models\User;

/**
 * Class CoursePolicy
 *
 * @author Mathrix Team
 * @package App\Policies
 */
class CoursePolicy extends BasePolicy
{
    /**
     * @param User $currentUser
     *
     * @return bool
     */
    public function add(User $currentUser)
    {
        if ($currentUser->tokenCan('admin')) {
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
        if ($currentUser->tokenCan('admin')) {
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
        if ($currentUser->tokenCan('admin')) {
            return true;
        }

        return false;
    }
}