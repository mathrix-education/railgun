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
abstract class PremiumTestCase extends PermissionTestCase
{
    /**
     * Initialize premium
     * @return User
     */
    public function initUser(): User
    {
        return User::inRandomOrder()->where('type', 'premium')->first();
    }
}