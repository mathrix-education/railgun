<?php
namespace Tests\Modules;

use Tests\ScopeTestCase;

/**
 * Class ModulesAsPremiumTest
 * Test modules as premium user
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix
 * @version 1.0.0
 * @package Tests\Modules
 */
class ModulesAsPremiumTest extends ScopeTestCase
{
    protected $subFactory = 'random';


    public function testIndex()
    {
        $this->sameAs('public');
    }


    public function testAdd()
    {
        $this->sameAs('public');
    }


    public function testEdit()
    {
        $this->sameAs('public');
    }


    public function testDelete()
    {
        $this->sameAs('public');
    }
}