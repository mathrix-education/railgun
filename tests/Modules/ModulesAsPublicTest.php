<?php
namespace Tests\Modules;

use Tests\ScopeTestCase;

/**
 * Class ModulesAsPublicTest
 * Test modules as public (non-logged user)
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix
 * @version 1.0.0
 * @package Tests\Modules
 */
class ModulesAsPublicTest extends ScopeTestCase
{
    protected $subFactory = "random";


    public function testIndex()
    {
        $this->genericIndexSuccess();
    }


    public function testAdd()
    {
        $this->genericAddUnauthorized();
    }


    public function testEdit()
    {
        $this->genericEditUnauthorized();
    }


    public function testDelete()
    {
        $this->genericDeleteUnauthorized();
    }
}