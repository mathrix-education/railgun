<?php

namespace Tests\Unit;

use App\Models\Class_;
use Tests\BaseTestCase;

/**
 * Class ClassUtilsTest.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 *
 * @version   4.0.3
 */
class ClassTest extends BaseTestCase
{
    public function testClassCursus()
    {
        $class = Class_::whereNotNull("parent_id")
            ->firstOrFail();
        $cursus = $class->cursus();

        $currentParentId = $class->id;

        /** @var Class_ $parent */
        foreach ($cursus as $parent) {
            if ($parent->id !== null) {
                $this->assertEquals($currentParentId, $parent->id);
                $currentParentId = $parent->parent_id;
            }
        }
    }
}
