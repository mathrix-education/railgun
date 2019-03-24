<?php

namespace Tests\Unit;

use App\Models\Grade;
use Tests\BaseTestCase;

/**
 * Class ClassTest.
 *
 * @author    Mathieu Bour <mathieu@mathrix.fr>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     4.0.3
 * @copyright Mathrix Education SA
 * @package   Tests\Unit
 */
class ClassTest extends BaseTestCase
{
    public function testClassCursus()
    {
        $class = Grade::whereNotNull("parent_id")
            ->firstOrFail();
        $cursus = $class->cursus();

        $currentParentId = $class->id;

        /** @var Grade $parent */
        foreach ($cursus as $parent) {
            if ($parent->id !== null) {
                $this->assertEquals($currentParentId, $parent->id);
                $currentParentId = $parent->parent_id;
            }
        }
    }
}
