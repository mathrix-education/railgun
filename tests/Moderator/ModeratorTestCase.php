<?php
namespace Tests\Moderator;

use App\Models\User;
use Tests\PermissionTestCase;

/**
 * Class ModeratorTestCase
 * Base class for moderator tests
 *
 * @package Tests\Moderator
 */
abstract class ModeratorTestCase extends PermissionTestCase
{
    /**
     * Initialize moderator
     * @return User
     */
    public function initUser(): User
    {
        return User::inRandomOrder()->where('type', '=', 'moderator')->first();
    }
}