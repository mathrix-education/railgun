<?php

declare(strict_types=1);

namespace Tests\Functional\Billing\Models;

use Laravel\Lumen\Testing\DatabaseTransactions;
use Railgun\Models\Training;
use Railgun\Models\User;
use Tests\Functional\Models\ModelTestCase;
use function factory;

/**
 * @coversDefaultClass \Railgun\Billing\Models\OwnsProducts
 */
class OwnsProductsTest extends ModelTestCase
{
    use DatabaseTransactions;

    /**
     * @covers ::owns
     */
    public function testOwnsWithPermissions(): void
    {
        /** @var User $user */
        $user = factory(User::class)->create(['permissions' => ['a']]);
        $this->assertTrue($user->owns(Training::random()));
    }
}
