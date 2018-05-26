<?php
namespace Tests\Stripe;

use App\Models\Purchase;
use App\Models\Section;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Passport\Passport;
use Tests\BaseTestCase;

class SectionBuyTest extends BaseTestCase
{


    /**
     * POST /sections/{id}/buy
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

        $this->call("post", "sections/{$section->id}/buy", ["stripe_token" => "tok_visa"]);

        $this->assertHttp200Success();
        $this->assertLogged("user_payment_succeed_for_section", $user);
        $this->assertInDatabase("purchases", [
            "user_id" => $user->id,
            "purchasable_id" => $section->id,
            "purchasable_type" => "section"
        ]);
    }


    /**
     * POST /sections/{id}/buy
     */
    public function testBuyAlreadyPurchased()
    {
        do {
            $purchase = Purchase::where("purchasable_type", "=", "section")
                ->firstOrFail();
        } while (!$purchase->purchasable->visible);
        Passport::actingAs($purchase->user, ["registered"]);

        $this->call("post", "sections/{$purchase->purchasable_id}/buy", ["stripe_token" => "tok_visa"]);

        $this->assertHttp400InvalidRequest();
    }


    /**
     * POST /sections/{id}/buy
     */
    public function testBuyInvalidToken()
    {
        $user = User::whereType("registered")->inRandomOrder()->firstOrFail();
        Passport::actingAs($user, [$user->type]);

        $userSectionIds = $user->purchases()
            ->where("purchasable_type", "=", "section")
            ->get()
            ->pluck("purchasable_id")
            ->toArray();
        $section = Section::whereVisible(true)
            ->whereNotIn("id", $userSectionIds)
            ->firstOrFail();

        $this->call("post", "sections/{$section->id}/buy", ["stripe_token" => "tok_chargeCustomerFail"]);

        $this->assertResponseStatus(400);
        $this->assertNotInDatabase("purchases", [
            "user_id" => $user->id,
            "purchasable_id" => $section->id,
            "purchasable_type" => "section"
        ]);
    }
}
