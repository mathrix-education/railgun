<?php

namespace App\Policies;

use App\Models\User;

/**
 * Class BasePolicy.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 *
 * @since     1.0.0
 *
 * @copyright Mathrix Education SA
 *
 * @method bool atLeastAdmin(User $currentUser)
 * @method bool atLeastModerator(User $currentUser)
 */
abstract class BasePolicy
{
    /**
     * Global search policy.
     *
     * @param User $currentUser
     *
     * @return bool
     */
    public function search(User $currentUser)
    {
        return $currentUser->isModerator();
    }
}
