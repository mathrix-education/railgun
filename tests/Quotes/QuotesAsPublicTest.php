<?php
namespace Tests\Quotes;

use App\Models\Quote;
use Tests\ScopeTestCase;

/**
 * Class CountriesAsPublicTest
 * Test quotes as public (non-logged user)
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix
 * @version 1.0.0
 * @package Tests\Quotes
 */
class QuotesAsPublicTest extends ScopeTestCase
{
    public function testIndex()
    {
        $this->genericIndexSuccess();
    }

    public function testRandom()
    {
        $this->call('get', '/quotes/random');

        // Assertions
        $this->assertHttp200Success();
        $this->assertJsonLength(1);
    }

    public function testRandomMultiple()
    {
        $quoteCountToFetch = $this->faker->numberBetween(1, Quote::count());
        $this->call('get', '/quotes/random/' . $quoteCountToFetch);

        // Assertions
        $this->assertHttp200Success();
        $this->assertJsonLength($quoteCountToFetch);
    }

    public function testAdd()
    {
        $this->genericAddUnauthorized();
    }

    public function testEdit()
    {
        $this->genericEditUnauthorized();
    }

    public function testDelete()
    {
        $this->genericDeleteUnauthorized();
    }
}