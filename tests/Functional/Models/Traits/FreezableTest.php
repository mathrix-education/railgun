<?php

declare(strict_types=1);

namespace Tests\Functional\Models\Traits;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Mockery;
use Mockery\MockInterface;
use Railgun\Exceptions\MissingScopeException;
use Railgun\Models\Traits\Freezable;
use Tests\ApplicationTestCase;

/**
 * @testdox Freezable Model trait
 * @coversDefaultClass \Railgun\Models\Traits\Freezable
 */
class FreezableTest extends ApplicationTestCase
{
    /**
     * @covers ::bootFreezable
     */
    public function testBootFreezable(): void
    {
        $instance = new class extends Model {
            use Freezable;

            public function isFrozen(): bool
            {
                return true;
            }

            public function fireSavingEvent(): void
            {
                $this->fireModelEvent('saving');
            }
        };

        $this->expectException(MissingScopeException::class);
        $instance->fireSavingEvent();
    }

    /**
     * @covers ::initializeFreezable
     */
    public function testInitializeFreezable(): void
    {
        $instance = new class extends Model {
            use Freezable;
        };
        $this->assertContains('frozen_until', $instance->getFillable());
    }

    /**
     * @covers ::getFrozenUntilColumn
     */
    public function testGetFrozenUntilColumn(): void
    {
        /** @var Model|Freezable|MockInterface $instance */
        $instance = Mockery::mock(Model::class, Freezable::class);
        $this->assertEquals('frozen_until', $instance->getFrozenUntilColumn());

        $instance = new class extends Model {
            use Freezable;

            protected string $frozenUntilColumn = 'foobar';
        };
        $this->assertEquals('foobar', $instance->getFrozenUntilColumn());
    }

    /**
     * @covers ::getFrozenScope
     */
    public function testGetFrozenScope(): void
    {
        /** @var Freezable|MockInterface $instance */
        $instance = Mockery::namedMock('Railgun\\Models\\Cherry', Freezable::class);
        $this->assertEquals('cherries:freeze', $instance->getFrozenScope());
    }

    /**
     * @covers ::isFrozen
     */
    public function testIsFrozen(): void
    {
        /** @var Model|Freezable|MockInterface $instance */
        $instance = Mockery::mock(Model::class, Freezable::class)->makePartial();

        $instance->frozen_until = Carbon::now()->addMonth();
        $instance->syncOriginal();
        $this->assertTrue($instance->isFrozen());

        $instance->frozen_until = Carbon::now()->subMonth();
        $instance->syncOriginal();
        $this->assertFalse($instance->isFrozen());

        $instance->frozen_until = null;
        $instance->syncOriginal();
        $this->assertFalse($instance->isFrozen());

        $instance->frozen_until = 'invalid string';
        $instance->syncOriginal();
        $this->assertFalse($instance->isFrozen());
    }
}
