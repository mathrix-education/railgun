<?php
namespace Tests\Countries;

/**
 * Class CountriesAsAdminTest
 * Test countries as admin
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix
 * @version 1.0.0
 * @package Tests\Countries
 */
class CountriesAsAdminTest extends CountriesAsModeratorTest
{
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
        $this->genericDelete();
        $this->assertHttp501NotImplemented();
    }
}