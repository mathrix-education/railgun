<?php
namespace Tests\Countries;

use Tests\ScopeTestCase;

/**
 * Class CountriesAsModeratorTest
 * Test countries as moderator
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix
 * @version 1.0.0
 * @package Tests\Countries
 */
class CountriesAsModeratorTest extends ScopeTestCase
{
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