<?php
namespace Tests\Quotes;

use Tests\ScopeTestCase;

/**
 * Class CountriesAsAdminTest
 * Test quotes as admin
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix
 * @version 1.0.0
 * @package Tests\Quotes
 */
class QuotesAsAdminTest extends ScopeTestCase
{
    public function testIndex()
    {
        $this->sameAs("public");
    }


    public function testRandom()
    {
        $this->sameAs("public");
    }


    public function testRandomMultiple()
    {
        $this->sameAs("public");
    }


    public function testAdd()
    {
        $this->genericAddSuccess();
    }


    public function testEdit()
    {
        $this->genericEditSuccess();
    }


    public function testDelete()
    {
        $this->genericDeleteSuccess();
    }
}