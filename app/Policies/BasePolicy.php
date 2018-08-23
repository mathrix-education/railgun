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
     * Dynamically check for permission based on method name.
     *
     * @see BasePolicy::before()
     *
     * @param string $name
     * @param array  $args
     *
     * @return bool
     */
    public function __call($name, $args)
    {
        $potentialScopeIndex = mb_strpos($name, "atLeast");

        if ($potentialScopeIndex !== false) {
            $potentialScope = mb_strtolower(mb_substr($name, $potentialScopeIndex + 7));

            if (\in_array($potentialScope, ["admin", "moderator"], true) && $args[0] instanceof User) {
                /** @var User $user */
                $user = $args[0];

                return $user->tokenCan($potentialScope);
            }
        }

        return false; // @codeCoverageIgnore
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
