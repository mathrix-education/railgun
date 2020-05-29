<?php

declare(strict_types=1);

namespace Tests\Functional\Models\Traits;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Mockery;
use Mockery\MockInterface;
use Railgun\Exceptions\MissingPermissionException;
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

        $this->expectException(MissingPermissionException::class);
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
        $this->assertContains('frozen_at', $instance->getFillable());
    }

    /**
     * @covers ::getFrozenAtColumn
     */
    public function testGetFrozenAtColumn(): void
    {
        /** @var Model|Freezable|MockInterface $instance */
        $instance = Mockery::mock(Model::class, Freezable::class);
        $this->assertEquals('frozen_at', $instance->getFrozenAtColumn());

        $instance = new class extends Model {
            use Freezable;

            public const FROZEN_AT = 'foobar';
        };
        $this->assertEquals('foobar', $instance->getFrozenAtColumn());
    }

    /**
     * @covers ::getFreezePermission
     */
    public function testGetFreezePermission(): void
    {
        /** @var Freezable|MockInterface $instance */
        $instance = Mockery::namedMock('Railgun\\Models\\Cherry', Freezable::class);
        $this->assertEquals('cherries:freeze', $instance->getFreezePermission());
    }

    /**
     * @covers ::isFrozen
     */
    public function testIsFrozen(): void
    {
        /** @var Model|Freezable|MockInterface $instance */
        $instance = Mockery::mock(Model::class, Freezable::class)->makePartial();

        $instance->frozen_at = Carbon::now()->addMonth();
        $instance->syncOriginal();
        $this->assertTrue($instance->isFrozen());

        $instance->frozen_at = Carbon::now()->subMonth();
        $instance->syncOriginal();
        $this->assertTrue($instance->isFrozen());

        $instance->frozen_at = null;
        $instance->syncOriginal();
        $this->assertFalse($instance->isFrozen());

        $instance->frozen_at = 'invalid string';
        $instance->syncOriginal();
        $this->assertFalse($instance->isFrozen());
    }
}
