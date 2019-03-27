<?php

namespace App\Policies;

use App\Models\User;

/**
 * Class DiscountPolicy.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     1.0.0
 * @copyright Mathrix Education SA
 * @package   App\Policies
 */
class DiscountPolicy extends BasePolicy
{
    /**
     * @param User $currentUser
     *
     * @return bool
     */
    public function index(User $currentUser)
    {
        return $currentUser->isAdmin();
    }


    /**
     * @param User $currentUser
     *
     * @return bool
     */
    public function get(User $currentUser)
    {
        return $currentUser->isAdmin();
    }


    /**
     * @param User $currentUser
     *
     * @return bool
     */
    public function add(User $currentUser)
    {
        return $currentUser->isAdmin();
    }


    /**
     * @param User $currentUser
     *
     * @return bool
     */
    public function patch(User $currentUser)
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
        return $currentUser->isAdmin();
    }
}
