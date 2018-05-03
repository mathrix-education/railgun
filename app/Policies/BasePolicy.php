<?php
namespace App\Policies;

use App\Models\User;

/**
 * Class BasePolicy
 *
 * @author Mathrix Back-End Team
 * @package App\Policies
 * @since 1.0.0
 * @copyright Mathrix Education SA
 *
 * @method bool atLeastAdmin(User $currentUser)
 * @method bool atLeastModerator(User $currentUser)
 */
abstract class BasePolicy
{
    /**
     * Allow admin to bypass policies: return true for them, null otherwise
     *
     * @param User $currentUser
     *
     * @return bool|null
     */
    public function before(User $currentUser)
    {
        if ($currentUser->tokenCan("admin")) {
            return true;
        } else {
            return null;
        }
    }


    public function adminOnly()
    {
        return false;
    }


    /**
     * Dynamically check for permission based on method name
     *
     * @see BasePolicy::before()
     *
     * @param string $name
     * @param array $args
     *
     * @return bool|null
     */
    public function __call($name, $args)
    {
        $potentialScopeIndex = strpos($name, "atLeast");

        if ($potentialScopeIndex !== false) {
            $potentialScope = strtolower(substr($name, $potentialScopeIndex + 7));

            if (in_array($potentialScope, ["admin", "moderator"]) && $args[0] instanceof User) {
                /** @var User $user */
                $user = $args[0];

                return $user->tokenCan($potentialScope);
            }
        }

        return null;
    }


    /**
     * Global search policy
     *
     * @param User $currentUser
     *
     * @return bool
     */
    public function search(User $currentUser)
    {
        return $this->atLeastModerator($currentUser);
    }
}
