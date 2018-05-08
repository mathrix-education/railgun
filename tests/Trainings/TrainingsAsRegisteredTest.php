<?php
namespace Tests\Trainings;

use App\Models\Training;
use App\Models\User;
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
            $user = User::whereHas("trainings")
                ->where("type", $this->user->type)
                ->inRandomOrder()
                ->firstOrFail();
            /** @var Training $training */
            $training = $user->trainings()
                ->inRandomOrder()
                ->firstOrFail();
        } else {
            /** @var User $user */
            $user = User::inRandomOrder()
                ->where("type", $this->user->type)
                ->firstOrFail();
            $userTrainingIds = $user->trainings->pluck("id")->toArray();
            /** @var Training $training */
            $training = Training::inRandomOrder()
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

        $this->assertTrue(isset($data->sections));
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
     * POST /training/{id}/buy
     */
    public function testBuy()
    {
        if ($this->user->type != "registered") {
            return;
        }

        $userTrainingIds = $this->user->trainings->pluck("id")->toArray();
        $training = Training::inRandomOrder()->whereNotIn("id", $userTrainingIds)->firstOrFail();

        $this->call("post", "/trainings/{$training->id}/buy", [
            "stripe_token" => "tok_visa"
        ]);

        $this->assertHttp200Success();
        $this->assertInDatabase("training_user", ["user_id" => $this->user->id, "training_id" => $training->id]);
    }
}
