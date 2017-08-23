<?php
namespace Tests\Registered;

use Tests\Registered\Traits\CountriesAsc;
use Tests\Registered\Traits\CountriesDesc;

class CountriesTest extends RegisteredTestCase
{
    use CountriesAsc, CountriesDesc;
}