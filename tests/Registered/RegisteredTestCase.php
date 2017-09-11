<?php
namespace Tests\Registered;

use App\Models\User;
use Tests\PermissionTestCase;

/**
 * Class RegisteredTestCase
 * Base class for registred tests
 *
 * @package Tests\Registered
 */
abstract class RegisteredTestCase extends PermissionTestCase
{
    /**
     * Initialize registered
     * @return User
     */
    public function initUser(): User
    {
        return User::inRandomOrder()->where('type', 'registered')->first();
    }
}