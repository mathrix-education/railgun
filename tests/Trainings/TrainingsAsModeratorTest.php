<?php

namespace Tests\Trainings;

/**
 * Class TrainingAsModeratorTest
 * Test training as moderator
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix SA
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
}
