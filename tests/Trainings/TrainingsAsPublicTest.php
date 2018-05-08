<?php
namespace Tests\Trainings;

use App\Models\Training;
use Tests\ScopeTestCase;

/**
 * Class TrainingAsPublicTest
 * Test training as public (non-logged user)
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
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
        $this->genericIndexSuccess();
    }


    /**
     * GET /training/{id}
     *
     * @throws \Exception
     */
    public function testGet()
    {
        $this->genericGetSuccess();
    }



    /**
     * GET /training/slug/{slug}
     *
     * @throws \Exception
     */
    public function testGetBySlug()
    {
        /** @var Training $training */
        $training = Training::random();
        $this->call("get", "/trainings/slug/{$training->slug}");

        $this->assertHttp200Success();
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
