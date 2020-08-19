<?php

declare(strict_types=1);

namespace Tests\Functional\Models;

use Laravel\Lumen\Testing\DatabaseTransactions;

/**
 * @coversDefaultClass \Railgun\Models\Country
 */
class CountryTest extends ModelTestCase
{
    use DatabaseTransactions;

    /**
     * @covers ::grades
     */
    public function testGrades(): void
    {
        $this->assertHasMany('grades');
    }
}
