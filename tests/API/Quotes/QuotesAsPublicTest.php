<?php

namespace Tests\API\Quotes;

use App\Models\Quote;
use Tests\API\ScopeTestCase;

/**
 * Class QuotesAsPublicTest
 * Test quotes as public (non-logged user).
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 *
 * @version   1.0.0
 */
class QuotesAsPublicTest extends ScopeTestCase
{
    /**
     * GET /quotes.
     *
     * @throws \Exception
     */
    public function testIndex()
    {
        $this->genericIndexSuccess();
    }

    /**
     * GET /quotes/{id}.
     *
     * @throws \Exception
     */
    public function testGet()
    {
        $this->genericGetSuccess();
    }

    /**
     * POST /quotes -> HTTP 401 unauthorized.
     *
     * @throws \Exception
     */
    public function testAdd()
    {
        $this->genericAddUnauthorized();
    }

    /**
     * PATCH /quotes/{id} -> HTTP 401 unauthorized.
     *
     * @throws \Exception
     */
    public function testEdit()
    {
        $this->genericEditUnauthorized();
    }

    /**
     * DELETE /quotes/{id} -> HTTP 401 unauthorized.
     *
     * @throws \Exception
     */
    public function testDelete()
    {
        $this->genericDeleteUnauthorized();
    }

    /**
     * GET /quotes/random.
     */
    public function testRandom()
    {
        $this->call("get", "/quotes/random");

        // Assertions
        $this->assertHttp200Success();
        $this->assertJsonLength(1);
    }

    /**
     * GET /quotes/random/{number}.
     */
    public function testRandomMultiple()
    {
        $quoteCountToFetch = $this->faker->numberBetween(1, Quote::count());
        $this->call("get", "/quotes/random/" . $quoteCountToFetch);

        // Assertions
        $this->assertHttp200Success();
        $this->assertJsonLength($quoteCountToFetch);
    }
}
