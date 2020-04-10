<?php

declare(strict_types=1);

namespace Tests\Functional\Collectors;

use Illuminate\Support\Facades\DB;
use Mockery;
use Mockery\MockInterface;
use Railgun\Collectors\UserCollector;
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
        $expectedGrades = DB::table('users')
            ->selectRaw('count(grade_id)')
            ->whereNotNull('grade_id')
            ->groupBy('grade_id')
            ->get()
            ->count();

        /** @var UserCollector|MockInterface $instance */
        $instance = Mockery::mock(UserCollector::class . '[gauge]');
        $instance->expects('gauge->set')->times($expectedGrades);
        $instance->compute();
    }
}
