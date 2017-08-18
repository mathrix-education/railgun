<?php
namespace Tests\Registered;

use App\Models\User;
use Tests\PermissionTestCase;

class RegisteredTestCase extends PermissionTestCase {
    public function initUser(): User
    {
        return User::inRandomOrder()->where('type', 'registered')->first();
    }
}