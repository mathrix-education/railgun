<?php
namespace App\Policies;

use App\Models\User;

/**
 * Class ReferencePolicy.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @since     4.3.0
 * @copyright Mathrix Education SA
 * @package   App\Policies
 */
class ReferencePolicy extends BasePolicy
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
