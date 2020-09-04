<?php

declare(strict_types=1);

namespace Tests\Functional\Models;

use Laravel\Lumen\Testing\DatabaseTransactions;
use Railgun\Models\Acquisition;
use Railgun\Models\Contracts\Sellable;
use Railgun\Models\User;
use function factory;

/**
 * @coversDefaultClass \Railgun\Models\Acquisition
 */
class AcquisitionTest extends ModelTestCase
{
    use DatabaseTransactions;

    /**
     * @covers ::product
     */
    public function testProduct(): void
    {
        $acquisition = factory(Acquisition::class)->create();
        $this->assertInstanceOf(Sellable::class, $acquisition->product);
    }

    /**
     * @covers ::user
     */
    public function testUser(): void
    {
        $acquisition = factory(Acquisition::class)->create();
        $this->assertInstanceOf(User::class, $acquisition->user);
    }
}
