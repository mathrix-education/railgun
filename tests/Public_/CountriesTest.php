<?php
namespace Tests\Public_;

use Tests\Public_\Traits\CountriesAsc;
use Tests\Public_\Traits\CountriesDesc;

class CountriesTest extends PublicTestCase
{
    use CountriesAsc, CountriesDesc;
}