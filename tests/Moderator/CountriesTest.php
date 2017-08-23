<?php
namespace Tests\Moderator;

use Tests\Moderator\Traits\CountriesAsc;
use Tests\Moderator\Traits\CountriesDesc;

class CountriesTest extends ModeratorTestCase
{
    use CountriesAsc, CountriesDesc;
}