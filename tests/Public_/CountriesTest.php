<?php
namespace Tests\Public_;

use Tests\Public_\Traits\CountriesAsc;

/**
 * Class CountriesTest
 * Test countries as public
 *
 * @package Tests\Public_
 */
class CountriesTest extends PublicTestCase
{
    use CountriesAsc;
}