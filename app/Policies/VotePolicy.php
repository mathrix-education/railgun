<?php
namespace App\Policies;

use App\Models\User;

/**
 * Class VotePolicy
 *
 * @author Mathrix Team
 * @package App\Policies
 */
class VotePolicy
{
    /**
     * @param User $currentUser
     *
     * @return bool
     */
    public function vote(User $currentUser)
    {
        if ($currentUser->tokenCan('registered')) {
            return true;
        }

        return false;
    }
}