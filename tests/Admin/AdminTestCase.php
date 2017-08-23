<?php
namespace Tests\Admin;

use App\Models\User;
use Tests\PermissionTestCase;

class AdminTestCase extends PermissionTestCase
{
    public function initUser(): User
    {
        return User::inRandomOrder()->where('type', 'admin')->first();
    }
}