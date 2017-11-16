<?php
namespace Tests\Activities;

use Tests\ScopeTestCase;

/**
 * Class ActivitiesAsAdminTest
 * Test activities as admin
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix
 * @version 1.0.0
 * @package Tests\Activities
 */
class ActivitiesAsAdminTest extends ScopeTestCase
{
    public function testIndex()
    {
        $this->sameAs('moderator');
    }


    public function testAdd()
    {
        $this->sameAs('moderator');
    }


    public function testEdit()
    {
        $this->sameAs('moderator');
    }


    public function testEditWithLink()
    {
        $this->sameAs('moderator');
    }


    public function testDelete()
    {
        $this->sameAs('moderator');
    }


    public function testMarkFavorite()
    {
        $this->sameAs('registered');
    }


    public function testMarkStatus()
    {
        $this->sameAs('registered');
    }
}