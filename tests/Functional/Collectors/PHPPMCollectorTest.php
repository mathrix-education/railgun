<?php

declare(strict_types=1);

namespace Tests\Functional\Collectors;

use Mockery;
use Mockery\MockInterface;
use Prometheus\Exception\MetricNotFoundException;
use Prometheus\Exception\MetricsRegistrationException;
use Railgun\Collectors\PHPPMCollector;
use Railgun\Support\PHPPMStatus;
use Tests\ApplicationTestCase;
use Tests\Functional\Support\PHPPMStatusTest;

/**
 * @coversDefaultClass \Railgun\Collectors\PHPPMCollector
 */
class PHPPMCollectorTest extends ApplicationTestCase
{
    /**
     * @covers ::compute
     *
     * @throws MetricNotFoundException
     * @throws MetricsRegistrationException
     */
    public function testCompute(): void
    {
        /** @var PHPPMStatus|MockInterface $instance */
        $instance = Mockery::mock(PHPPMStatus::class . '[getStatus]');
        $instance->expects('getStatus')->andReturns(PHPPMStatusTest::STATUS_ARRAY);

        /** @var PHPPMCollector|MockInterface $collector */
        $collector = Mockery::mock(PHPPMCollector::class . '[gauge]');
        $collector->expects('gauge->set')->times(10); // 6 status + 4 workers
        $collector->compute($instance);
    }
}
