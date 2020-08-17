<?php

declare(strict_types=1);

namespace Tests\Functional\Models;

use Railgun\Models\User;
use function factory;

/**
 * @coversDefaultClass \Railgun\Models\BaseModel
 */
class BaseModelTest extends ModelTestCase
{
    /**
     * @covers ::getGlobalKey
     */
    public function testGetGlobalIdExisting(): void
    {
        $user = User::random();
        $this->assertEquals("user_{$user->id}", $user->getGlobalKey());
    }

    /**
     * @covers ::getGlobalKey
     */
    public function testGetGlobalIdNonExisting(): void
    {
        /** @var User $user */
        $user = factory(User::class)->make();
        $this->assertFalse($user->exists);
        $this->assertMatchesRegularExpression('/user_\d+/', $user->getGlobalKey());
        $this->assertTrue($user->exists);
    }
}
