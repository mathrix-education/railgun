<?php
namespace Tests\API\Disciplines;

use App\Models\Class_;
use App\Models\Discipline;

/**
 * Class DisciplinesAsAdminTest.
 * Test disciplines as admin.
 *
 * @author    Mathieu Bour <mathieu@mathrix.fr>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     1.0.0
 * @copyright Mathrix Education SA
 * @package   Tests\API\Disciplines
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
        $visible = $this->faker->boolean;
        $class = Class_::random();

        $this->genericEditSuccess([
            "link" => [
                "classes" => [
                    [
                        "id" => $class->id,
                        "visible" => $visible
                    ]
                ]
            ]
        ]);

        $discipline = Discipline::findOrFail($this->editModelId);

        $this->assertInDatabase("class_discipline", [
            "class_id" => $class->id,
            "discipline_id" => $this->editModelId,
            "visible" => $visible
        ]);
        $this->assertEquals($discipline->classes()->count(), 1);
    }

    /**
     * DELETE /disciplines/{id}
     *
     * @throws \Exception
     */
    public function testDelete()
    {
        $discipline = factory(Discipline::class)->create();
        $this->deleteModelId = $discipline->id;
        $this->genericDelete();
    }
}
