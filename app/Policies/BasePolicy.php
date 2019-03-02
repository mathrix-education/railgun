<?php
namespace App\Policies;

use App\Models\User;

/**
 * Class BasePolicy.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     1.0.0
 * @copyright Mathrix Education SA
 * @package   App\Policies
 *
 * @method bool atLeastAdmin(User $currentUser)
 * @method bool atLeastModerator(User $currentUser)
 */
abstract class BasePolicy
{
    /**
     * Check, if the current user can access to target user content.
     *
     * @param User     $currentUser       The current user.
     * @param User|int $targetUser        The target user.
     * @param string   $minimumPermission The minimum permission level.
     *
     * @return bool
     */
    public function someone(User $currentUser, $targetUser, string $minimumPermission = "admin"): bool
    {
        if (is_int($targetUser)) {
            /** @var User $targetUser */
            $targetUser = User::findOrFail($targetUser);
        }

        return $currentUser->isAdmin() || $currentUser->id === $targetUser->id;
    }

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
