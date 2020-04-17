<?php

declare(strict_types=1);

namespace Tests\Functional\Billing\Models;

use Railgun\Billing\Models\Premium;
use Tests\ApplicationTestCase;

/**
 * @coversDefaultClass \Railgun\Billing\Models\Premium
 */
class PremiumTest extends ApplicationTestCase
{
    public function testToArray(): void
    {
        $this->assertIsArray((new Premium())->toArray());
    }
}
