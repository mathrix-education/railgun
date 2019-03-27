<?php

namespace App\Policies;

use App\Models\Module;
use App\Models\User;

/**
 * Class ModulePolicy.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     1.0.0
 * @copyright Mathrix Education SA
 * @package   App\Policies
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
     * @param User $requester
     * @param Module $module
     *
     * @return bool
     */
    public function get(User $requester, Module $module)
    {
        return !$module->premium || $requester->isModerator() || $requester->premium;
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
    public function patch(User $currentUser)
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


    /**
     * @param User $currentUser
     *
     * @return bool
     */
    public function markIndex(User $currentUser)
    {
        return $currentUser->isAdmin();
    }
}
