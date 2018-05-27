<?php
namespace Tests\API\Trainings;

use App\Models\Discount;
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
    /**
     * GET /trainings/{id}/recursive as buyer
     */
    public function testRecursiveAsBuyer()
    {
        /** @var User $user */
        $user = User::whereHas("purchases", function (Builder $query) {
            $query->where("purchasable_type", "=", "training");
        })
            ->where("type", "registered")
            ->inRandomOrder()
            ->firstOrFail();
        $trainingIds = $user->purchases()
            ->where("purchasable_type", "=", "training")
            ->pluck("purchasable_id")
            ->toArray();
        /** @var Training $training */
        $training = Training::whereVisible(true)->whereIn("id", $trainingIds)->firstOrFail();
        Passport::actingAs($user, ["registered"]);

        $this->call("get", "/trainings/{$training->id}/recursive");

        $responseData = $this->getResponseData();

        $this->assertHttp200Success();
        $this->assertTrue(isset($responseData->sections), "No sections were sent.");

        /** @var Section $section */
        foreach ($responseData->sections as $section) {
            $this->assertTrue($section->visible, "An invisible section was sent.");
            /** @var Lesson $lesson */
            foreach ($section->lessons as $lesson) {
                $this->assertTrue($lesson->visible, "An invisible lesson was sent.");
                if (!empty($lesson->module)) {
                    $this->assertTrue($lesson->visible, "An invisible module was sent.");
                }
            }
        }
    }


    /**
     * GET /trainings/{id}/recursive as non buyer
     */
    public function testRecursiveAsNonBuyer()
    {
        /** @var User $user */
        $user = User::inRandomOrder()
            ->where("type", "registered")
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
        Passport::actingAs($user, ["registered"]);

        $this->call("get", "/trainings/{$training->id}/recursive");

        $this->assertHttp401Unauthorized();
    }
}
