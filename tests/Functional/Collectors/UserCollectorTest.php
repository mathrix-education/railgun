<?php

declare(strict_types=1);

namespace Tests\Functional\Collectors;

use Mockery;
use Mockery\MockInterface;
use Railgun\Collectors\UserCollector;
use Railgun\Models\Grade;
use Tests\ApplicationTestCase;

/**
 * @coversDefaultClass \Railgun\Collectors\UserCollector
 */
class UserCollectorTest extends ApplicationTestCase
{
    /**
     * @covers ::register
     */
    public function testRegister(): void
    {
        /** @var UserCollector|MockInterface $instance */
        $instance = Mockery::mock(UserCollector::class . '[gauge]');
        $instance->expects('gauge');
        $instance->register();
    }

    /**
     * @covers ::compute
     */
    public function testCompute(): void
    {
        /** @var UserCollector|MockInterface $instance */
        $instance = Mockery::mock(UserCollector::class . '[gauge]');
        $instance->expects('gauge->set')->times(Grade::query()->count());
        $instance->compute();
    }
}
