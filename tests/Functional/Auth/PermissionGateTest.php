<?php

declare(strict_types=1);

namespace Tests\Functional\Auth;

use Illuminate\Support\Facades\Gate;
use Railgun\Auth\PermissionGate;
use Railgun\Models\User;
use Tests\ApplicationTestCase;
use function factory;

/**
 * @coversDefaultClass \Railgun\Auth\PermissionGate
 */
class PermissionGateTest extends ApplicationTestCase
{
    /**
     * @covers ::__invoke
     */
    public function testInvoke(): void
    {
        /** @var User $user */
        $user              = factory(User::class)->create();
        $user->permissions = ['a', 'b', 'd'];
        $user->save();

        $gate = Gate::forUser($user);

        $this->assertTrue($gate->check(PermissionGate::NAME, 'a'));
        $this->assertTrue($gate->check(PermissionGate::NAME, 'b'));
        $this->assertFalse($gate->check(PermissionGate::NAME, 'c'));
        $this->assertTrue($gate->check(PermissionGate::NAME, 'd'));
    }
}
