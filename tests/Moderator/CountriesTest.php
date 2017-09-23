<?php
namespace Tests\Moderator;

use Tests\Moderator\Traits\CountriesAsc;
use Tests\Moderator\Traits\CountriesDesc;

/**
 * Class CountriesTest
 * Test countries as moderator
 *
 * @author Mathrix Team
 * @package Tests\Moderator
 */
class CountriesTest extends ModeratorTestCase
{
    use CountriesAsc, CountriesDesc;
}