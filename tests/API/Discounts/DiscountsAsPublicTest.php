<?php
namespace Tests\API\Discounts;

use App\Models\Discount;
use Tests\API\ScopeTestCase;

/**
 * Class DiscountsAsPublicTest.
 * Test discounts as public (non-logged user).
 *
 * @author    Mathieu Bour <mathieu@mathrix.fr>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @version   4.0.3
 * @copyright Mathrix Education SA
 * @package   Tests\API\Discounts
 */
class DiscountsAsPublicTest extends ScopeTestCase
{
    /**
     * GET /discounts.
     *
     * @throws \Exception
     */
    public function testIndex()
    {
        $this->genericIndexUnauthorized();
    }

    /**
     * GET /discounts/{id} -> HTTP 401 unauthorized.
     *
     * @throws \Exception
     */
    public function testGet()
    {
        $this->genericGetUnauthorized();
    }

    /**
     * POST /discounts -> HTTP 401 unauthorized.
     *
     * @throws \Exception
     */
    public function testAdd()
    {
        $this->genericAddUnauthorized();
    }

    /**
     * PATCH /discounts/{id} -> HTTP 401 unauthorized.
     *
     * @throws \Exception
     */
    public function testEdit()
    {
        $this->genericEditUnauthorized();
    }

    /**
     * DELETE /discounts/{id} -> HTTP 401 unauthorized.
     *
     * @throws \Exception
     */
    public function testDelete()
    {
        $this->genericDeleteUnauthorized();
    }

    /**
     * POST /discounts/check.
     */
    public function testCheck()
    {
        /** @var Discount $discount */
        $discount = Discount::where("counter", ">", 0)->firstOrFail();

        $this->post("/discounts/check", ["code" => $discount->code])
            ->assertHttp200Success()
            ->assertJsonKeys(["id", "code", "counter"]);
    }
}
