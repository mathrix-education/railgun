<?php
namespace App\Policies;

use App\Models\User;

/**
 * Class ActivityPolicy.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     1.0.0
 * @copyright Mathrix Education SA
 * @package   App\Policies
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
        return $currentUser->isModerator();
    }

    /**
     * @return bool
     */
    public function byNotion()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function byModule()
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
        return $currentUser->isModerator();
    }

    /**
     * @param User $currentUser
     *
     * @return bool
     */
    public function edit(User $currentUser)
    {
        return $currentUser->isModerator();
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
