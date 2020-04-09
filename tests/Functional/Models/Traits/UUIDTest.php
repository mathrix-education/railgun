<?php

declare(strict_types=1);

namespace Tests\Functional\Models\Traits;

use Illuminate\Database\Eloquent\Model;
use Railgun\Models\Traits\UUID;
use Tests\ApplicationTestCase;

/**
 * @testdox UUID Model trait
 * @coversDefaultClass \Railgun\Models\Traits\UUID
 */
class UUIDTest extends ApplicationTestCase
{
    /**
     * @covers ::bootUUID
     */
    public function testCreatingUUIDNotSet(): void
    {
        $instance = new class extends Model {
            use UUID;

            public function fireCreatingEvent(): void
            {
                $this->fireModelEvent('creating');
            }
        };

        $instance->fireCreatingEvent();
        $this->assertMatchesRegularExpression(
            '/[0-9a-fA-F]{8}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{12}/',
            $instance->id,
        );
    }

    /**
     * @covers ::bootUUID
     */
    public function testCreatingUUIDSet(): void
    {
        $uuid = $this->faker->uuid;

        $instance = new class extends Model {
            use UUID;

            public function fireCreatingEvent(): void
            {
                $this->fireModelEvent('creating');
            }
        };

        $instance->id = $uuid;
        $instance->fireCreatingEvent();

        $this->assertEquals($uuid, $instance->id);
    }

    /**
     * @covers ::initializeUUID
     */
    public function testInitialize(): void
    {
        $instance = new class extends Model {
            use UUID;
        };

        $this->assertEquals('string', $instance->getKeyType());
        $this->assertFalse($instance->getIncrementing());
    }
}
