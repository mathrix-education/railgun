<?php
namespace Tests\Trainings;

use App\Models\Lesson;
use App\Models\Purchase;
use App\Models\Section;
use App\Models\Training;
use App\Models\User;
use App\Utils\StripeUtils;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Passport\Passport;

/**
 * Class TrainingAsPublicTest
 * Test training as registered user
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 * @version 1.0.0
 * @package Tests\Training
 */
class TrainingsAsRegisteredTest extends TrainingsAsPublicTest
{
    public function generic($isBuyer, $url, $assertSuccess = false)
    {
        if ($isBuyer) {
            /** @var User $user */
            $user = User::whereHas("purchases", function (Builder $query) {
                $query->where("purchasable_type", "=", "training");
            })
                ->where("type", $this->user->type)
                ->inRandomOrder()
                ->firstOrFail();
            $trainingIds = $user->purchases()
                ->where("purchasable_type", "=", "training")
                ->pluck("purchasable_id")
                ->toArray();
            /** @var Training $training */
            $training = Training::whereVisible(true)->whereIn("id", $trainingIds)->firstOrFail();
        } else {
            /** @var User $user */
            $user = User::inRandomOrder()
                ->where("type", $this->user->type)
                ->firstOrFail();
            $userTrainingIds = $user->purchases()
                ->where("purchasable_type", "=", "training")
                ->pluck("purchasable_id")
                ->toArray();
            /** @var Training $training */
            $training = Training::inRandomOrder()
                ->where("visible", true)
                ->whereNotIn("id", $userTrainingIds)
                ->firstOrFail();
        }

        Passport::actingAs($user, [$user->type]);
        $this->call("get", str_replace("{id}", $training->id, $url));

        if ($isBuyer || $assertSuccess) {
            $this->assertHttp200Success();
        } else {
            $this->assertHttp401Unauthorized();
        }
    }


    /**
     * Assert response is a recursive training
     *
     * @throws \Exception
     */
    public function assertIsRecursive()
    {
        $data = $this->getResponseData();

        $this->assertTrue(isset($data->sections), "No sections were sent.");

        /** @var Section $section */
        foreach ($data->sections as $section) {
            $this->assertTrue($section->visible || $this->user->isModerator(),
                "An invisible section was sent.");
            /** @var Lesson $lesson */
            foreach ($section->lessons as $lesson) {
                $this->assertTrue($lesson->visible || $this->user->isModerator(),
                    "An invisible lesson was sent.");

                if (!empty($lesson->module)) {
                    $this->assertTrue($lesson->visible || $this->user->isModerator(),
                        "An invisible module was sent.");
                }
            }
        }
    }


    /**
     * GET /trainings/{id}/recursive as buyer
     *
     * @throws \Exception
     */
    public function testRecursiveAsBuyer()
    {
        $this->generic(true, "/trainings/{id}/recursive");
        $this->assertIsRecursive();
    }


    /**
     * GET /trainings/{id}/recursive as non buyer
     */
    public function testRecursiveAsNonBuyer()
    {
        $this->generic(false, "/trainings/{id}/recursive");
    }


    /**
     * POST /trainings/{id}/buy
     *
     * @throws \Exception
     */
    public function testBuy()
    {
        if ($this->user->type != "registered") {
            return;
        }

        $userTrainingIds = $this->user->purchases()
            ->where("purchasable_type", "=", "training")
            ->get()
            ->pluck("purchasable_id")
            ->toArray();
        $training = Training::inRandomOrder()
            ->where("visible", "=", true)
            ->whereNotIn("id", $userTrainingIds)
            ->firstOrFail();

        $this->call("post", "/trainings/{$training->id}/buy", [
            "stripe_token" => "tok_visa"
        ]);

        $responseData = $this->getResponseData();

        $this->assertHttp200Success();
        $this->assertTrue(isset($responseData->subscriptions));
        $this->assertTrue(!empty($responseData->purchases));
        $this->assertInDatabase("purchases", [
            "user_id" => $this->user->id,
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
        // Skip for moderators and admins
        if ($this->user->type != "registered") {
            return;
        }

        $userTrainingIds = $this->user->purchases()
            ->where("purchasable_type", "=", "training")
            ->get()
            ->pluck("purchasable_id")
            ->toArray();

        $training = Training::inRandomOrder()
            ->whereNotIn("id", $userTrainingIds)
            ->where("visible", "=", true)
            ->firstOrFail();

        $this->call("post", "/trainings/{$training->id}/buy", [
            "stripe_token" => "tok_visa",
            "premium" => true
        ]);

        $responseData = $this->getResponseData();

        $this->assertHttp200Success();
        $this->assertTrue(isset($responseData->subscriptions));
        $this->assertTrue(!empty($responseData->purchases));
        $this->assertInDatabase("purchases", [
            "user_id" => $this->user->id,
            "purchasable_id" => $training->id,
            "purchasable_type" => "training"
        ]);
        $this->assertInDatabase("subscriptions", [
            "user_id" => $this->user->id,
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
        $buyer = User::whereType($this->user->type)
            ->whereNotIn("id", $userIdsWhoBoughtThisTraining)
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
}
