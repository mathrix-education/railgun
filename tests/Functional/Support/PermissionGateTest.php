<?php

declare(strict_types=1);

namespace Tests\Functional\Support;

use Illuminate\Support\Facades\Gate;
use Railgun\Models\User;
use Railgun\Support\PermissionGate;
use Tests\ApplicationTestCase;
use function factory;

/**
 * @coversDefaultClass \Railgun\Support\PermissionGate
 */
class PermissionGateTest extends ApplicationTestCase
{
    /**
     * @covers ::__invoke
     */
    public function testInvoke(): void
    {
        /** @var User $user */
        $user         = factory(User::class)->create();
        $user->scopes = ['a', 'b', 'd'];
        $user->save();

        $gate = Gate::forUser($user);

        $this->assertTrue($gate->check(PermissionGate::NAME, 'a'));
        $this->assertTrue($gate->check(PermissionGate::NAME, 'b'));
        $this->assertFalse($gate->check(PermissionGate::NAME, 'c'));
        $this->assertTrue($gate->check(PermissionGate::NAME, 'd'));
    }
}
