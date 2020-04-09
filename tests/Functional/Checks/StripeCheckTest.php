<?php

declare(strict_types=1);

namespace Tests\Functional\Checks;

use Railgun\Checks\StripeCheck;
use Tests\ApplicationTestCase;

/**
 * @coversDefaultClass \Railgun\Checks\StripeCheck
 */
class StripeCheckTest extends ApplicationTestCase
{
    /**
     * @covers ::__construct
     * @covers ::execute
     */
    public function testExecute(): void
    {
        $this->assertNotNull($this->app->make(StripeCheck::class)->execute());
    }
}
