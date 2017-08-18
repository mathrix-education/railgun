<?php
namespace Tests\Premium;

use App\Models\User;
use Tests\PermissionTestCase;

class PremiumTestCase extends PermissionTestCase {
    public function initUser(): User
    {
        return User::inRandomOrder()->where('type', 'premium')->first();
    }
}