<?php
namespace Tests\API\Classes;
use App\Models\Discipline;

/**
 * Class ClassesAsAdminTest
 * Test classes as admin
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 * @version 1.0.0
 * @package Tests\Classes
 */
class ClassesAsAdminTest extends ClassesAsModeratorTest
{
    /**
     * POST /classes
     *
     * @throws \Exception
     */
    public function testAdd()
    {
        $this->genericAddSuccess();
    }


    /**
     * PATCH /classes/{id}
     *
     * @throws \Exception
     */
    public function testEdit()
    {
        $this->genericEditSuccess();
    }


    /**
     * PATCH /classes/{id}
     *
     * @throws \Exception
     */
    public function testEditWithDisciplines()
    {
        $disciplineIds = Discipline::inRandomOrder()->limit(3)->get(["id"])->pluck("id");

        $this->genericEditSuccess([
            "link" => [
                "disciplines" => $disciplineIds
            ]
        ]);

        foreach ($disciplineIds as $disciplineId) {
            $this->assertInDatabase("class_discipline", [
                "class_id" => $this->editModelId,
                "discipline_id" => $disciplineId
            ]);
        }
    }

    /**
     * DELETE /classes/{id} -> HTTP 501 not_implemented
     *
     * @throws \Exception
     */
    public function testDelete()
    {
        $this->genericDelete();
        $this->assertHttp501NotImplemented();
    }
}
