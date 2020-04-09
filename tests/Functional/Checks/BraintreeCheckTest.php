<?php

declare(strict_types=1);

namespace Tests\Functional\Checks;

use Railgun\Checks\BraintreeCheck;
use Tests\ApplicationTestCase;

/**
 * @coversDefaultClass \Railgun\Checks\BraintreeCheck
 */
class BraintreeCheckTest extends ApplicationTestCase
{
    /**
     * @covers ::__construct
     * @covers ::execute
     */
    public function testExecute(): void
    {
        $this->assertNotNull($this->app->make(BraintreeCheck::class)->execute());
    }
}
