<?php
namespace Tests\Moderator;

use App\Models\User;
use Tests\PermissionTestCase;

class ModeratorTestCase extends PermissionTestCase {
    public function initUser(): User
    {
        return User::inRandomOrder()->where('type', 'moderator')->first();
    }
}