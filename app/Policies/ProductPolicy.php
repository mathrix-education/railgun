<?php

namespace App\Policies;

use App\Models\User;

/**
 * Class ProductPolicy.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 *
 * @since
 *
 * @copyright Mathrix Education SA
 */
class ProductPolicy extends BasePolicy
{
    /**
     * @return bool
     */
    public function index()
    {
        return true;
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
    public function add(User $currentUser)
    {
        return $currentUser->isAdmin();
    }

    /**
     * @param User $currentUser
     *
     * @return bool
     */
    public function edit(User $currentUser)
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
