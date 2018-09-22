<?php

namespace App\Policies;

use App\Models\User;

/**
 * Class ModulePolicy.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 *
 * @since     1.0.0
 *
 * @copyright Mathrix Education SA
 */
class ModulePolicy extends BasePolicy
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
    public function byActivity()
    {
        return true;
    }

    // Get policy directly handled in the controller

    /**
     * @param User $currentUser
     *
     * @return bool
     */
    public function add(User $currentUser)
    {
        return $currentUser->isModerator();
    }

    /**
     * @param User $currentUser
     *
     * @return bool
     */
    public function edit(User $currentUser)
    {
        return $currentUser->isRedactor();
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
