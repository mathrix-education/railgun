<?php
namespace Tests\Stripe;

use App\Models\Discount;
use App\Models\Purchase;
use App\Models\Training;
use App\Models\User;
use App\Utils\StripeUtils;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Passport\Passport;
use Tests\BaseTestCase;

class TrainingBuyTest extends BaseTestCase
{
    /**
     * Base test for all training purchases
     *
     * @param User $user
     * @param User|null $sponsor
     * @param bool $withPremium
     *
     * @return Training
     */
    public function buy(User $user, $sponsor, bool $withPremium): Training
    {
        Passport::actingAs($user, [$user->type]);

        $userTrainingIds = $user->purchases()
            ->where("purchasable_type", "=", "training")
            ->get()
            ->pluck("purchasable_id")
            ->toArray();

        $buyRequestData = [
            "stripe_token" => "tok_visa"
        ];

        // Handle premium pack purchase
        if ($withPremium) {
            $buyRequestData["premium"] = true;
        }

        // Handle sponsored purchase
        if ($sponsor !== null) {
            $buyRequestData["sponsor_email"] = $sponsor->email;
        }

        $training = Training::inRandomOrder()
            ->where("visible", "=", true)
            ->whereNotIn("id", $userTrainingIds)
            ->firstOrFail();

        $this->call("post", "/trainings/{$training->id}/buy", $buyRequestData);

        return $training;
    }


    /**
     * POST /training/{id}/buy
     *
     * As registered user who did not bought the training, without sponsor nor premium plan
     */
    public function testBuy()
    {
        $user = User::whereNull("stripe_id")->firstOrFail();

        $training = $this->buy($user, null, false);

        $responseData = $this->getResponseData();

        $this->assertHttp200Success();
        $this->assertTrue(!empty($responseData->purchases));
        $this->assertInDatabase("purchases", [
            "user_id" => $user->id,
            "purchasable_id" => $training->id,
            "purchasable_type" => "training"
        ]);
    }


    /**
     * POST /trainings/{id}/buy with ethe additional premium subscription
     *
     * @throws \Exception
     */
    public function testBuyWithPremium()
    {
        $user = User::whereNull("stripe_id")->firstOrFail();

        $training = $this->buy($user, null, true);

        $responseData = $this->getResponseData();

        $this->assertHttp200Success();
        $this->assertTrue(isset($responseData->subscriptions));
        $this->assertTrue(!empty($responseData->purchases));
        $this->assertInDatabase("purchases", [
            "user_id" => $user->id,
            "purchasable_id" => $training->id,
            "purchasable_type" => "training"
        ]);
        $this->assertInDatabase("subscriptions", [
            "user_id" => $user->id,
            "name" => "premium",
            "stripe_plan" => "v3_annually",
            "trial_ends_at" => StripeUtils::getNextJuly()
        ]);
    }


    /**
     * @throws \Exception
     */
    public function testBuyWithSponsorship()
    {
        /** @var Purchase $purchase */
        $purchase = Purchase::wherePurchasableType("training")->inRandomOrder()->firstOrFail();
        /** @var Training $training */
        $training = $purchase->purchasable;
        /** @var User $sponsor */
        $sponsor = $purchase->user;

        /** @var array $userIdsWhoBoughtThisTraining */
        $userIdsWhoBoughtThisTraining = Purchase::where([
            ["purchasable_id", "=", $training->id],
            ["purchasable_type", "=", "training"]
        ])
            ->pluck("user_id")
            ->toArray();

        /** @var User $buyer */
        $buyer = User::whereType("registered")
            ->whereNotIn("id", $userIdsWhoBoughtThisTraining)
            ->whereNull("stripe_id")
            ->firstOrFail();

        Passport::actingAs($buyer, [$buyer->type]);

        $this->call("post", "/trainings/{$training->id}/buy", [
            "stripe_token" => "tok_visa",
            "sponsor_email" => $sponsor->email
        ]);

        $newPurchase = Purchase::where([
            ["user_id", "=", $buyer->id],
            ["purchasable_id", "=", $training->id],
            ["purchasable_type", "=", "training"]
        ])->first();
        $responseData = $this->getResponseData();

        $this->assertHttp200Success();
        $this->assertTrue(isset($responseData->subscriptions));
        $this->assertTrue(!empty($responseData->purchases));
        $this->assertInstanceOf(Purchase::class, $newPurchase);
        $this->assertEquals($training->price - StripeUtils::SPONSORSHIP_DISCOUNT, $newPurchase->price);
    }


    /**
     * POST /trainings/{id}/buy
     *
     * @throws \Exception
     */
    public function testBuyWithDiscount()
    {
        $user = User::whereNull("stripe_id")->firstOrFail();
        Passport::actingAs($user, ["registered"]);

        $userTrainingIds = $user->purchases()
            ->where("purchasable_type", "=", "training")
            ->get()
            ->pluck("purchasable_id")
            ->toArray();
        $training = Training::inRandomOrder()
            ->where("visible", "=", true)
            ->whereNotIn("id", $userTrainingIds)
            ->firstOrFail();

        /** @var Discount $discount */
        $discount = Discount::where([
            ["counter", ">", 0]
        ])->inRandomOrder()->firstOrFail();

        $countBefore = $discount->counter;

        $this->call("post", "/trainings/{$training->id}/buy", [
            "stripe_token" => "tok_visa",
            "discount_code" => $discount->code
        ]);

        $responseData = $this->getResponseData();
        $discount = Discount::find($discount->id);
        $potentialPurchase = Purchase::where([
            ["user_id", "=", $user->id],
            ["purchasable_id", "=", $training->id],
            ["purchasable_type", "=", "training"]
        ])->first();

        $this->assertHttp200Success();
        $this->assertEquals($countBefore - 1, $discount->counter);
        $this->assertTrue(isset($responseData->subscriptions));
        $this->assertTrue(!empty($responseData->purchases));
        $this->assertNotNull($potentialPurchase);
        $this->assertGreaterThan($potentialPurchase->price, $potentialPurchase->purchasable->price);
    }
}
