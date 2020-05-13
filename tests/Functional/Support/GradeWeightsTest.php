<?php

declare(strict_types=1);

namespace Tests\Functional\Support;

use Railgun\Models\Grade;
use Railgun\Support\GradeWeights;
use Tests\ApplicationTestCase;

class GradeWeightsTest extends ApplicationTestCase
{
    public function testGetSql(): void
    {
        $weights = new GradeWeights();
        $sql     = $weights->getSql(Grade::random()->id, 'foo');
        $this->assertNotNull($sql);
    }
}
