<?php
namespace Tests\Premium;

use Tests\Premium\Traits\CountriesAsc;
use Tests\Premium\Traits\CountriesDesc;

class CountriesTest extends PremiumTestCase
{
    use CountriesAsc, CountriesDesc;
}