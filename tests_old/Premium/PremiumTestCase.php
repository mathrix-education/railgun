<?php
namespace Tests\Premium;

use App\Models\User;
use Tests\PermissionTestCase;

/**
 * Class PremiumTestCase
 * Base class for premium tests
 *
 * @author Mathrix Team
 * @package Tests\Premium
 */
abstract class PremiumTestCase extends PermissionTestCase
{
    /**
     * Initialize premium
     *
     * @return User
     */
    public function initUser(): User
    {
        /** @var User $user */
        $user = User::inRandomOrder()->where('type', 'premium')->first();

        return $user;
    }
}