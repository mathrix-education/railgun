<?php
namespace App\Policies;

use App\Models\Setting;
use App\Models\User;

/**
 * Class SettingPolicy.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     4.0.3
 * @copyright Mathrix Education SA
 * @package   App\Policies
 */
class SettingPolicy extends BasePolicy
{
    /**
     * @param User    $currentUser
     * @param Setting $setting
     *
     * @return bool
     */
    public function read(User $currentUser, Setting $setting)
    {
        return $currentUser->isAdmin() || $setting->visible;
    }

    /**
     * @param User $currentUser
     *
     * @return bool
     */
    public function write(User $currentUser)
    {
        return $currentUser->isAdmin();
    }

    /**
     * @param User $currentUser
     *
     * @return bool
     */
    public function forgot(User $currentUser)
    {
        return $currentUser->isAdmin();
    }
}
