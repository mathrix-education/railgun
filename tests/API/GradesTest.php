<?php

namespace Tests\API;

use App\Models\Chapter;
use App\Models\Grade;
use Mathrix\Lumen\Tests\Standard\StandardGetTest;
use Mathrix\Lumen\Tests\Standard\StandardIndexTest;
use Mathrix\Lumen\Tests\Standard\StandardPatchTest;
use Mathrix\Lumen\Tests\Standard\StandardPostTest;

/**
 * Class GradesTest.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0
 */
class GradesTest extends APITestCase
{
    use StandardIndexTest, StandardGetTest, StandardPostTest, StandardPatchTest;

    /**
     * DELETE /grades/{gradeId}
     */
    public function testDelete()
    {
        $grade = Grade::random();
        $pivot = Grade::random(["id", "!=", $grade->id]);

        $grade->children()->update(["parent_id" => $pivot->id]);
        $grade->chapters()->update(["grade_id" => $pivot->id]);
        $grade->users()->update(["grade_id" => $pivot->id]);

        $this->assertRestDeleteSuccess(["conditions" => ["id", "=", $grade->id]]);
    }


    /**
     * GET /grades/slug/{gradeSlug}
     */
    public function testBySlug(): void
    {
        $this->assertRestGetBySuccess("slug");
    }
}
