<?php
namespace Tests\Premium;

use App\Models\User;
use Tests\PermissionTestCase;

/**
 * Class PremiumTestCase
 * Base class for premium tests
 *
 * @package Tests\Premium
 */
class PremiumTestCase extends PermissionTestCase
{
    public function initUser(): User
    {
        return User::inRandomOrder()->where('type', 'premium')->first();
    }
}