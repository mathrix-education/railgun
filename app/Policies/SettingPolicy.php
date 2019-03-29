<?php

namespace App\Policies;

use App\Models\Setting;
use App\Models\User;

/**
 * Class SettingPolicy.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 4.0.3
 */
class SettingPolicy extends BasePolicy
{
    /**
     * @param User $currentUser
     * @param Setting $setting
     *
     * @return bool
     */
    public function read(User $currentUser, Setting $setting)
    {
        return $currentUser->tokenCan("admin") || $setting->visible;
    }


    /**
     * @param User $currentUser
     *
     * @return bool
     */
    public function write(User $currentUser)
    {
        return $currentUser->tokenCan("admin");
    }


    /**
     * @param User $currentUser
     *
     * @return bool
     */
    public function forgot(User $currentUser)
    {
        return $currentUser->tokenCan("admin");
    }
}
