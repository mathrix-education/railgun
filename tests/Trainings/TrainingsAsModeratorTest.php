<?php

namespace Tests\Trainings;

/**
 * Class TrainingAsModeratorTest
 * Test training as moderator
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 * @version 1.0.0
 * @package Tests\Training
 */
class TrainingsAsModeratorTest extends TrainingsAsRegisteredTest
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
     * PATCH /training/{id}
     *
     * @throws \Exception
     */
    public function testEdit()
    {
        $this->genericEditSuccess();
    }


    /**
     * DELETE /training/{id}
     *
     * @throws \Exception
     */
    public function testDelete()
    {
        $this->genericDelete();
    }


    /**
     * GET /trainings/{id} as buyer
     */
    public function testGetAsBuyer()
    {
        $this->generic(true, "/trainings/{id}", true);
    }


    /**
     * GET /trainings/{id} as non buyer
     */
    public function testGetAsNonBuyer()
    {
        $this->generic(false, "/trainings/{id}", true);
    }


    /**
     * GET /trainings/{id}/recursive as buyer
     *
     * @throws \Exception
     */
    public function testRecursiveAsBuyer()
    {
        $this->generic(true, "/trainings/{id}/recursive", true);
        $this->assertIsRecursive();
    }


    /**
     * GET /trainings/{id}/recursive as non buyer
     *
     * @throws \Exception
     */
    public function testRecursiveAsNonBuyer()
    {
        $this->generic(false, "/trainings/{id}/recursive", true);
        $this->assertIsRecursive();
    }
}
