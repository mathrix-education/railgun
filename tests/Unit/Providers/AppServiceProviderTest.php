<?php

declare(strict_types=1);

namespace Tests\Unit\Providers;

use Illuminate\Support\Facades\Gate;
use Railgun\Models\User;
use Tests\BaseTestCase;
use function factory;

class AppServiceProviderTest extends BaseTestCase
{
    public function testScopeGate(): void
    {
        /** @var User $user */
        $user         = factory(User::class)->create();
        $user->scopes = ['a', 'b', 'd'];
        $user->save();

        $gate = Gate::forUser($user);

        $this->assertTrue($gate->check('scope', 'a'));
        $this->assertTrue($gate->check('scope', 'b'));
        $this->assertFalse($gate->check('scope', 'c'));
        $this->assertTrue($gate->check('scope', 'd'));
    }
}
