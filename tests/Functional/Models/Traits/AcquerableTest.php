<?php

declare(strict_types=1);

namespace Tests\Functional\Models\Traits;

use Laravel\Lumen\Testing\DatabaseTransactions;
use Tests\Functional\Models\ModelTestCase;

/**
 * @coversDefaultClass \Railgun\Models\Traits\Acquerable
 */
class AcquerableTest extends ModelTestCase
{
    use DatabaseTransactions;

    /**
     * @testWith ["Railgun\\Models\\Chapter"]
     *           ["Railgun\\Models\\Pack"]
     *           ["Railgun\\Models\\Training"]
     *
     * @param string $modelClass The model class.
     */
    public function testOwners(string $modelClass): void
    {
        $this->modelClass = $modelClass;
        $this->assertMorphToMany('owners');
    }
}
