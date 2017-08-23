<?php
namespace Tests\Registered;

use Tests\Registered\Traits\CountriesDesc;
use Tests\Registered\Traits\CountriesAsc;

class CountriesTest extends RegisteredTestCase
{
    use CountriesAsc, CountriesDesc;
}