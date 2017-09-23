<?php
namespace Tests\Registered;

use App\Models\User;
use Tests\PermissionTestCase;

/**
 * Class RegisteredTestCase
 * Base class for registered tests
 *
 * @author Mathrix Team
 * @package Tests\Registered
 */
abstract class RegisteredTestCase extends PermissionTestCase
{
    /**
     * Initialize registered
     *
     * @return User
     */
    public function initUser(): User
    {
        /** @var User $user */
        $user = User::inRandomOrder()->where('type', 'registered')->first();

        return $user;
    }
}