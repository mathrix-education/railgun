<?php
namespace Tests\Disciplines;

use App\Models\Class_;
use Tests\ScopeTestCase;

/**
 * Class DisciplinesAsPublicTest
 * Test disciplines as public (non-logged user)
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 * @version 1.0.0
 * @package Tests\Disciplines
 */
class DisciplinesAsPublicTest extends ScopeTestCase
{
    /**
     * GET /disciplines
     *
     * @throws \Exception
     */
    public function testIndex()
    {
        $this->genericIndexSuccess();
    }


    /**
     * POST /disciplines -> HTTP 401 unauthorized
     *
     * @throws \Exception
     */
    public function testAdd()
    {
        $this->genericAddUnauthorized();
    }


    /**
     * PATCH /disciplines/{id} -> HTTP 401 unauthorized
     *
     * @throws \Exception
     */
    public function testEdit()
    {
        $this->genericEditUnauthorized();
    }


    /**
     * DELETE /disciplines/{id} -> HTTP 401 unauthorized
     *
     * @throws \Exception
     */
    public function testDelete()
    {
        $this->genericDeleteUnauthorized();
    }


    /**
     * GET /classes/by-class/{class_id}
     */
    public function testByClass()
    {
        $this->genericBySuccess(Class_::class);
    }
}
