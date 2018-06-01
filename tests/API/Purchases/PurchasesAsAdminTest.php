<?php
namespace Tests\API\Purchases;

use App\Models\Training;
use App\Models\User;

/**
 * Class PurchasesAsAdminTest
 * Test purchases as admin
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 * @version 4.0.4
 * @package Tests\API\Purchases
 */
class PurchasesAsAdminTest extends PurchasesAsModeratorTest
{
    /**
     * POST /purchases
     */
    public function testAddTraining()
    {
        $customer = User::where("type", "=", "registered")
            ->whereNotNull("stripe_id")
            ->firstOrFail();
        $customerTrainingIds = $customer->purchases()
            ->where("purchasable_type", "=", "training")
            ->get()
            ->pluck("purchasable_id")
            ->toArray();
        $training = Training::inRandomOrder()
            ->whereNotIn("id", $customerTrainingIds)
            ->firstOrFail();

        $this->post("/purchases", [
            "user_id" => $customer->id,
            "purchasable_type" => "training",
            "purchasable_id" => $training->id
        ]);

        $this->assertHttp200Success();
        $this->assertInDatabase("purchases", [
            "user_id" => $customer->id,
            "purchasable_type" => "training",
            "purchasable_id" => $training->id
        ]);
    }
}
