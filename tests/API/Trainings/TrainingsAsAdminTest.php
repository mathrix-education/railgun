<?php
namespace Tests\API\Trainings;

/**
 * Class TrainingAsAdminTest
 * Test training as admin
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 * @version 1.0.0
 * @package Tests\Training
 */
class TrainingsAsAdminTest extends TrainingsAsModeratorTest
{
    /**
     * GET /training
     *
     * @throws \Exception
     */
    public function testIndex()
    {
        $this->genericIndexSuccess();
    }


    /**
     * POST /training
     *
     * @throws \Exception
     */
    public function testAdd()
    {
        $this->genericAddSuccess();
    }


    /**
     * POST /training
     * With a different name/slug couple
     *
     * @throws \Exception
     */
    public function testAddWithDifferentSlug()
    {
        $this->genericAddSuccess(["slug" => $this->faker->slug()]);
    }


    /**
     * PATCH /trainings/{id}
     *
     * @throws \Exception
     */
    public function testEdit()
    {
        $this->genericEditSuccess();
    }


    /**
     * DELETE /trainings/{id}
     *
     * @throws \Exception
     */
    public function testDelete()
    {
        $this->genericDelete();
    }
}
