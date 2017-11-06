<?php
namespace Tests\Quotes;

use Tests\ScopeTestCase;

/**
 * Class CountriesAsModeratorTest
 * Test quotes as moderator
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix
 * @version 1.0.0
 * @package Tests\Quotes
 */
class QuotesAsModeratorTest extends ScopeTestCase
{
    public function testIndex()
    {
        $this->sameAs('public');
    }


    public function testRandom()
    {
        $this->sameAs('public');
    }


    public function testRandomMultiple()
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