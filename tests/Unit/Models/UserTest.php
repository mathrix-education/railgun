<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use Illuminate\Support\Facades\Hash;
use Railgun\Models\User;
use Tests\BaseTestCase;

/**
 * @coversDefaultClass \Railgun\Models\User
 */
class UserTest extends BaseTestCase
{
    /**
     * @covers ::setPasswordAttribute
     */
    public function testSetPasswordAttribute(): void
    {
        $user           = new User();
        $password       = '123456';
        $user->password = $password;

        $this->assertNotEquals($password, $user->password);
        $this->assertTrue(Hash::check($password, $user->password));
        $this->assertFalse(Hash::needsRehash($user->password));
        $this->assertNotEmpty($user->getAttribute('password'));
    }
}
