<?php
namespace Tests\Trainings;

use Tests\ScopeTestCase;

/**
 * Class TrainingAsPublicTest
 * Test training as public (non-logged user)
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix
 * @version 1.0.0
 * @package Tests\Training
 */
class TrainingsAsPublicTest extends ScopeTestCase
{
    /**
     * GET /training/index
     *
     * @throws \Exception
     */
    public function testIndex()
    {
        $this->genericIndexUnauthorized();
    }


    /**
     * POST /training -> HTTP 401 unauthorized
     *
     * @throws \Exception
     */
    public function testAdd()
    {
        $this->genericAddUnauthorized();
    }


    /**
     * PATCH /training/{id} -> HTTP 401 unauthorized
     *
     * @throws \Exception
     */
    public function testEdit()
    {
        $this->genericEditUnauthorized();
    }


    /**
     * DELETE /training/{id} -> HTTP 401 unauthorized
     *
     * @throws \Exception
     */
    public function testDelete()
    {
        $this->genericDeleteUnauthorized();
    }
}