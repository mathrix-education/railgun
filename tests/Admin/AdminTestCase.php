<?php
namespace Tests\Admin;

use App\Models\User;
use Tests\PermissionTestCase;

/**
 * Class AdminTestCase
 * Base class for admin tests
 *
 * @package Tests\Admin
 */
abstract class AdminTestCase extends PermissionTestCase
{
    /**
     * Initialize admin user
     * @return User
     */
    public function initUser(): User
    {
        return User::inRandomOrder()->where('type', '=', 'admin')->first();
    }
}