<?php

declare(strict_types=1);

namespace Tests\Functional\Checks;

use Railgun\Checks\MailjetCheck;
use Tests\ApplicationTestCase;

/**
 * @coversDefaultClass \Railgun\Checks\MailjetCheck
 */
class MailjetCheckTest extends ApplicationTestCase
{
    /**
     * @covers ::__construct
     * @covers ::execute
     */
    public function testExecute(): void
    {
        $this->assertNotNull($this->app->make(MailjetCheck::class)->execute());
    }
}
