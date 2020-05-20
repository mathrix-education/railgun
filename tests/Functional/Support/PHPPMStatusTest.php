<?php

declare(strict_types=1);

namespace Tests\Functional\Support;

use Mockery;
use Mockery\MockInterface;
use Railgun\Support\PHPPMStatus;
use Tests\ApplicationTestCase;

/**
 * @coversDefaultClass \Railgun\Support\PHPPMStatus
 */
class PHPPMStatusTest extends ApplicationTestCase
{
    public const STATUS_YAML  = <<<'YAML'
    Read configuration /var/www/ppm.json, modified by command arguments.
    /var/www
    
    status: healthy
    workers: 
            total: 4
            ready: 4
            busy: 0
            created: 0
            registered: 0
            closed: 0
    handled_requests: 8117
    handled_requests_per_worker: 
            5501: 96
            5502: 12
            5503: 1
            5504: 0
    YAML;
    public const STATUS_ARRAY = [
        'status'                      => 'healthy',
        'workers'                     => [
            'total'      => 4,
            'ready'      => 4,
            'busy'       => 0,
            'created'    => 0,
            'registered' => 0,
            'closed'     => 0,
        ],
        'handled_requests'            => 8117,
        'handled_requests_per_worker' => [
            5501 => 96,
            5502 => 12,
            5503 => 1,
            5504 => 0,
        ],
    ];

    /**
     * @covers ::getStatus
     */
    public function testGetStatus(): void
    {
        /** @var PHPPMStatus|MockInterface $instance */
        $instance = Mockery::mock(PHPPMStatus::class . '[getRawStatus]');
        $instance->expects('getRawStatus')->andReturns(self::STATUS_YAML);

        $data = $instance->getStatus();

        $this->assertEquals('healthy', $data['status']);
        $this->assertEquals(4, $data['workers']['ready']);
    }

    /**
     * @covers ::reload
     */
    public function testReload(): void
    {
        /** @var PHPPMStatus|MockInterface $instance */
        $instance = Mockery::mock(PHPPMStatus::class . '[getStatus]');
        $instance->expects('getStatus')->andReturns(self::STATUS_ARRAY);

        $instance->reload(); // First writes
        $instance->reload(); // Second do nothing
    }

    /**
     * @covers ::toArray
     */
    public function testToArray(): void
    {
        /** @var PHPPMStatus|MockInterface $instance */
        $instance = Mockery::mock(PHPPMStatus::class . '[getStatus]');
        $instance->expects('getStatus')->andReturns(self::STATUS_ARRAY);

        $this->assertEquals(self::STATUS_ARRAY, $instance->toArray());
    }

    /**
     * @covers ::__isset
     */
    public function testIsset(): void
    {
        /** @var PHPPMStatus|MockInterface $instance */
        $instance = Mockery::mock(PHPPMStatus::class . '[getStatus]');
        $instance->expects('getStatus')->andReturns(self::STATUS_ARRAY);

        $this->assertTrue(isset($instance->status));
    }

    /**
     * @covers ::__get
     */
    public function testGet(): void
    {
        /** @var PHPPMStatus|MockInterface $instance */
        $instance = Mockery::mock(PHPPMStatus::class . '[getStatus]');
        $instance->expects('getStatus')->andReturns(self::STATUS_ARRAY);

        $this->assertEquals('healthy', $instance->status);
    }
}
