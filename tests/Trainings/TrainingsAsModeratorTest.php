<?php

namespace Tests\Trainings;

/**
 * Class TrainingAsModeratorTest
 * Test training as moderator
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix
 * @version 1.0.0
 * @package Tests\Training
 */
class TrainingsAsModeratorTest extends TrainingsAsRegisteredTest
{
    /**
     * GET /training
     */
    public function testIndex()
    {
        $this->genericIndexSuccess();
    }

    /**
     * POST /training
     */
    public function testAdd()
    {
        $this->genericAddSuccess();
    }


    /**
     * PATCH /training/{id}
     */
    public function testEdit()
    {
        $this->genericEditSuccess();
    }


    /**
     * DELETE /training/{id}
     */
    public function testDelete()
    {
        $this->genericDelete();
    }
}