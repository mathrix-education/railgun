<?php
namespace App\Policies;


use App\Models\Setting;
use App\Models\User;

class SettingPolicy extends BasePolicy
{
    public function get(User $currentUser, Setting $setting)
    {
        return $currentUser->isAdmin() || $setting->visible;
    }
}
