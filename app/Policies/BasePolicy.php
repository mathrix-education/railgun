<?php
namespace App\Policies;

use App\Models\User;

/**
 * Class BasePolicy
 *
 * @author Mathrix Team
 * @package App\Policies
 */
abstract class BasePolicy
{
    /**
     * Allow admin to bypass policies
     *
     * @param User $currentUser
     *
     * @return bool|null
     */
    public function before(User $currentUser)
    {
        if ($currentUser->tokenCan('admin')) {
            return true;
        } else {
            return null;
        }
    }
}