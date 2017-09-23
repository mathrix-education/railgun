<?php
namespace Tests\Premium;

use Tests\Premium\Traits\CountriesAsc;
use Tests\Premium\Traits\CountriesDesc;

/**
 * Class CountriesTest
 * Test countries as premium
 *
 * @author Mathrix Team
 * @package Tests\Premium
 */
class CountriesTest extends PremiumTestCase
{
    use CountriesAsc, CountriesDesc;
}