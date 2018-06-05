<?php
namespace Tests\Stripe;

use App\Models\Purchase;
use App\Models\Section;
use App\Models\User;
use Laravel\Passport\Passport;
use Tests\BaseTestCase;

/**
 * Class SectionBuyTest
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @package Tests\Stripe
 * @since 4.0.3
 * @copyright Mathrix Education SA
 */
class SectionBuyTest extends BaseTestCase
{
    /**
     * POST /purchases/buy/section/{section_id}
     */
    public function testBuyNotPurchased()
    {
        $user = User::where("type", "=", "registered")
            ->whereNull("stripe_id")
            ->firstOrFail();
        Passport::actingAs($user, ["registered"]);

        $userSectionIds = $user->purchases()
            ->where("purchasable_type", "=", "section")
            ->get()
            ->pluck("purchasable_id")
            ->toArray();
        $section = Section::whereVisible(true)
            ->whereNotIn("id", $userSectionIds)
            ->firstOrFail();

        $this->call("post", "/purchases/buy/section/{$section->id}", ["stripe_token" => "tok_visa"]);

        $this->assertHttp200Success();
        $this->assertLogged("user_payment_succeed_for_section", $user);
        $this->assertInDatabase("purchases", [
            "user_id" => $user->id,
            "purchasable_id" => $section->id,
            "purchasable_type" => "section"
        ]);
    }


    /**
     * POST /purchases/buy/section/{section_id}
     */
    public function testBuyAlreadyPurchased()
    {
        do {
            $purchase = Purchase::where("purchasable_type", "=", "section")
                ->firstOrFail();
        } while (!$purchase->purchasable->visible);
        Passport::actingAs($purchase->user, ["registered"]);

        $this->call("post", "/purchases/buy/section/{$purchase->purchasable_id}", ["stripe_token" => "tok_visa"]);

        $this->assertHttp400InvalidRequest();
    }


    /**
     * POST /purchases/buy/section/{section_id}
     */
    public function testBuyInvalidToken()
    {
        $user = User::whereType("registered")
            ->whereNull("stripe_id")
            ->inRandomOrder()
            ->firstOrFail();
        Passport::actingAs($user, [$user->type]);

        $userSectionIds = $user->purchases()
            ->where("purchasable_type", "=", "section")
            ->get()
            ->pluck("purchasable_id")
            ->toArray();
        $section = Section::whereVisible(true)
            ->whereNotIn("id", $userSectionIds)
            ->firstOrFail();

        $this->call("post", "/purchases/buy/section/{$section->id}", ["stripe_token" => "dqsddsqqsd"]);

        $this->assertResponseStatus(400);
        $this->assertNotInDatabase("purchases", [
            "user_id" => $user->id,
            "purchasable_id" => $section->id,
            "purchasable_type" => "section"
        ]);
    }


    /**
     * POST /purchases
     */
    public function testAdd()
    {
        $user = User::random(["type", "=", "admin"]);
        Passport::actingAs($user, ["admin"]);

        $customer = User::where("type", "=", "registered")
            ->whereNotNull("stripe_id")
            ->firstOrFail();
        $customerSectionIds = $customer->purchases()
            ->where("purchasable_type", "=", "section")
            ->get()
            ->pluck("purchasable_id")
            ->toArray();
        $section = Section::inRandomOrder()
            ->whereNotIn("id", $customerSectionIds)
            ->firstOrFail();

        $this->post("/purchases", [
            "user_id" => $customer->id,
            "purchasable_type" => "section",
            "purchasable_id" => $section->id
        ]);

        $this->assertHttp200Success();
        $this->assertInDatabase("purchases", [
            "user_id" => $customer->id,
            "purchasable_type" => "section",
            "purchasable_id" => $section->id
        ]);
    }
}
