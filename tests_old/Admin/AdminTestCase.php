<?php
namespace Tests\Admin;

use App\Models\User;
use Tests\PermissionTestCase;

/**
 * Class AdminTestCase
 * Base class for admin tests
 *
 * @author Mathrix Team
 * @package Tests\Admin
 */
abstract class AdminTestCase extends PermissionTestCase
{
    /**
     * Initialize admin
     *
     * @return User
     */
    public function initUser(): User
    {
        /** @var User $user */
        $user = User::inRandomOrder()->where('type', '=', 'admin')->first();

        return $user;
    }
}