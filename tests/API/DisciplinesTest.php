<?php

namespace Tests\API;

use App\Models\Chapter;
use App\Models\Discipline;
use App\Models\Grade;
use Illuminate\Support\Arr;
use Mathrix\Lumen\Tests\REST\Standard\StandardGetTest;
use Mathrix\Lumen\Tests\REST\Standard\StandardIndexTest;
use Mathrix\Lumen\Tests\REST\Standard\StandardPatchTest;
use Mathrix\Lumen\Tests\REST\Standard\StandardPostTest;
use Tests\APITestCase;

/**
 * Class DisciplinesTest.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 */
class DisciplinesTest extends APITestCase
{
    use StandardIndexTest, StandardPostTest, StandardGetTest, StandardPatchTest;


    /**
     * DELETE /disciplines/{disciplineId}
     */
    public function testDelete(): void
    {
        /** @var Discipline $discipline */
        $discipline = Discipline::random();
        $pivot = Discipline::random(["id", "!=", $discipline->id]);

        $discipline->chapters()->update(["discipline_id" => $pivot->id]);

        $this->assertRestDeleteSuccess(["conditions" => ["id", "=", $discipline->id]]);
    }


    /**
     * GET /disciplines/by-grade/{gradeId}
     */
    public function testByGrade()
    {
        $this->assertRestBySuccess(Grade::class);
    }


    /**
     * GET /disciplines/by-grade/{gradeId}
     * Test that only visible disciplines are visible for a given grade
     */
    public function testByGradeOnlyVisible()
    {
        $chapter = Chapter::random();
        Chapter::query()
            ->where("discipline_id", "=", $chapter->discipline_id)
            ->where("grade_id", "=", $chapter->grade_id)
            ->update(["visible" => false]);

        $this->json("get", "/disciplines/by-grade/{$chapter->grade_id}/0/100");

        $this->assertOpenAPIResponse("/disciplines/by-grade/{gradeId}/{page}/{perPage}");
        $disciplineIds = array_values(Arr::pluck($this->getJsonResponseContent()->data, "id"));
        $this->assertTrue(!in_array($chapter->discipline_id, $disciplineIds),
            "Found discipline id {$chapter->discipline_id} in [" . implode(", ", $disciplineIds) . "]");
    }


    /**
     * GET /disciplines/slug/{disciplineSlug}
     */
    public function testBySlug(): void
    {
        $this->assertRestGetBySuccess("slug");
    }
}
