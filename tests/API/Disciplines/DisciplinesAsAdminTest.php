<?php

namespace Tests\API\Disciplines;

use App\Models\Class_;

/**
 * Class DisciplinesAsAdminTest
 * Test disciplines as admin.
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 *
 * @version 1.0.0
 */
class DisciplinesAsAdminTest extends DisciplinesAsModeratorTest
{
    /**
     * POST /disciplines.
     *
     * @throws \Exception
     */
    public function testAdd()
    {
        $this->genericAddSuccess();
    }

    /**
     * PATCH /disciplines/{id}.
     *
     * @throws \Exception
     */
    public function testEdit()
    {
        $this->genericEditSuccess();
    }

    /**
     * PATCH /disciplines/{id}.
     *
     * @throws \Exception
     */
    public function testEditWithClasses()
    {
        $classIds = Class_::inRandomOrder()->limit(3)->get(["id"])->pluck("id");

        $this->genericEditSuccess([
            "link" => [
                "classes" => $classIds,
            ],
        ]);

        foreach ($classIds as $classId) {
            $this->assertInDatabase("class_discipline", [
                "class_id" => $classId,
                "discipline_id" => $this->editModelId,
            ]);
        }
    }

    /**
     * DELETE /disciplines/{id} -> HTTP 501 not_implemented.
     *
     * @throws \Exception
     */
    public function testDelete()
    {
        $this->genericDelete();
        $this->assertHttp501NotImplemented();
    }
}
