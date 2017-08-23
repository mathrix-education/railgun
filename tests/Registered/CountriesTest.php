<?php
namespace Tests\Registered;

use Tests\Registered\Traits\CountriesAsc;
use Tests\Registered\Traits\CountriesDesc;

/**
 * Class CountriesTest
 * Test countries as registered
 * @package Tests\Registered
 */
class CountriesTest extends RegisteredTestCase
{
    use CountriesAsc, CountriesDesc;
}