<?php

declare(strict_types=1);

namespace Tests\Functional\Support;

use Carbon\Carbon;
use Railgun\Support\Timestamps;
use Tests\ApplicationTestCase;

/**
 * @coversDefaultClass \Railgun\Support\Timestamps
 */
class TimestampsTest extends ApplicationTestCase
{
    /**
     * @return mixed[][] The testing dataset.
     */
    public function createFactoryDataset(): array
    {
        return [
            ['-1 month', 90],
            ['-3 months', 0],
        ];
    }

    /**
     * @dataProvider createFactoryDataset
     * @testdox      generates coherent timestamps using ($start, $bias)
     * @covers ::factory
     *
     * @param string $start The range starting date
     * @param int    $bias  The generation bias.
     */
    public function testFactory(string $start, int $bias): void
    {
        $timestamps = Timestamps::factory($start, $bias);
        $this->assertGreaterThanOrEqual($timestamps->createdAt->getTimestamp(), $timestamps->updatedAt->getTimestamp());
        $this->assertGreaterThanOrEqual($timestamps->updatedAt->getTimestamp(), $timestamps->deletedAt->getTimestamp());
        $this->assertGreaterThanOrEqual($timestamps->deletedAt->getTimestamp(), Carbon::now()->getTimestamp());
    }
}
