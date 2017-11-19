<?php
namespace Tests\Modules;

use Tests\ScopeTestCase;

/**
 * Class ModulesAsPublicTest
 * Test modules as registered user
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix
 * @version 1.0.0
 * @package Tests\Modules
 */
class ModulesAsRegisteredTest extends ScopeTestCase
{
    protected $subFactory = "random";


    public function testIndex()
    {
        $this->sameAs("public");
    }


    public function testAdd()
    {
        $this->sameAs("public");
    }


    public function testEdit()
    {
        $this->sameAs("public");
    }


    public function testDelete()
    {
        $this->sameAs("public");
    }
}