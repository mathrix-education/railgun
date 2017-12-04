<?php

namespace Tests\Sections;

use App\Models\Module;

/**
 * Class SectionAsModeratorTest
 * Test section as moderator
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix SA
 * @version 1.0.0
 * @package Tests\Section
 */
class SectionsAsModeratorTest extends SectionsAsRegisteredTest
{
    /**
     * GET /section
     *
     * @throws \Exception
     */
    public function testIndex()
    {
        $this->genericIndexSuccess();
    }

    /**
     * POST /section
     *
     * @throws \Exception
     */
    public function testAdd()
    {
        $this->genericAddSuccess();
    }

    /**
     * PATCH /section/{id}
     *
     * @throws \Exception
     */
    public function testEdit()
    {
        $this->genericEditSuccess();
    }

    /**
     * POST /section
     *
     * @throws \Exception
     */
    public function testEditWithLink()
    {
        $link = [
            "modules" => $this->faker->randomElements(
                Module::inRandomOrder()->pluck("id")->toArray(),
                $this->faker->numberBetween(3, 5)
            )
        ];

        $this->genericEditSuccess(["link" => $link]);

        foreach ($link["modules"] as $order => $moduleId) {
            $this->assertInDatabase("module_section", [
                "section_id" => $this->editModelId,
                "module_id" => $moduleId,
                "order" => $order
            ]);
        }
    }


    /**
     * DELETE /section/{id}
     *
     * @throws \Exception
     */
    public function testDelete()
    {
        $this->genericDelete();
    }
}
