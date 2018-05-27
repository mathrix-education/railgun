<?php
namespace Tests\API\Discounts;
use App\Models\Discount;

/**
 * Class DiscountsAsRegisteredTest
 * Test discounts as registered user
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 * @version 4.0.3
 * @package Tests\Discounts
 */
class DiscountsAsRegisteredTest extends DiscountsAsPublicTest
{
    /**
     * POST /discounts/check
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
