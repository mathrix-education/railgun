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
use Tests\APIStatelessTestCase;

/**
 * Class DisciplinesTest.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 */
class DisciplinesTest extends APIStatelessTestCase
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
     * GET /disciplines/slug/{disciplineSlug}
     */
    public function testBySlug(): void
    {
        $this->assertRestGetBySuccess("slug");
    }
}
