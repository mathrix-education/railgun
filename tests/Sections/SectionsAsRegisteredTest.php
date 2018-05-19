<?php
namespace Tests\Sections;

use App\Models\Lesson;
use App\Models\Purchase;
use App\Models\Section;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Passport\Passport;

/**
 * Class SectionAsPublicTest
 * Test section as registered user
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 * @version 1.0.0
 * @package Tests\Section
 */
class SectionsAsRegisteredTest extends SectionsAsPublicTest
{
    public function generic($isBuyer, $url, $assertSuccess = false)
    {
        if ($isBuyer) {
            /** @var User $user */
            $user = User::whereHas("purchases", function (Builder $query) {
                $query->where("purchasable_type", "=", "section");
            })
                ->where("type", $this->user->type)
                ->inRandomOrder()
                ->firstOrFail();
            /** @var Purchase $purchase */
            $sectionsIds = $user->purchases()
                ->where("purchasable_type", "=", "section")
                ->pluck("purchasable_id")
                ->toArray();
            /** @var Section $section */
            $section = Section::whereVisible(true)->whereIn("id", $sectionsIds)->firstOrFail();
        } else {
            /** @var User $user */
            $user = User::inRandomOrder()
                ->where("type", $this->user->type)
                ->firstOrFail();
            $userSectionIds = $user->purchases()
                ->where("purchasable_type", "=", "section")
                ->pluck("purchasable_id")
                ->toArray();
            /** @var Section $section */
            $section = Section::inRandomOrder()
                ->where("visible", true)
                ->whereNotIn("id", $userSectionIds)
                ->firstOrFail();
        }

        Passport::actingAs($user, [$user->type]);
        $this->call("get", str_replace("{id}", $section->id, $url));

        if ($isBuyer || $assertSuccess) {
            $this->assertHttp200Success();
        } else {
            $this->assertHttp401Unauthorized();
        }
    }


    /**
     * Assert response is a recursive section
     *
     * @throws \Exception
     */
    public function assertIsRecursive()
    {
        $data = $this->getResponseData();

        $this->assertTrue(isset($data->lessons), "No sections were sent.");

        /** @var Lesson $lesson */
        foreach ($data->lessons as $lesson) {
            $this->assertTrue($lesson->visible || $this->user->isModerator(),
                "An invisible lesson was sent.");

            if (!empty($lesson->module)) {
                $this->assertTrue($lesson->visible || $this->user->isModerator(),
                    "An invisible module was sent.");
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
        $this->generic(true, "/sections/{id}/recursive");
        $this->assertIsRecursive();
    }


    /**
     * GET /trainings/{id}/recursive as non buyer
     */
    public function testRecursiveAsNonBuyer()
    {
        $this->generic(false, "/sections/{id}/recursive");
    }


    /**
     * POST /sections/{id}/buy
     */
    public function testBuyNotPurchased()
    {
        // Skip for moderators and admins
        if ($this->user->type != "registered") {
            return;
        }

        $userSectionIds = $this->user->purchases()
            ->where("purchasable_type", "=", "section")
            ->get()
            ->pluck("purchasable_id")
            ->toArray();
        $section = Section::whereVisible(true)
            ->whereNotIn("id", $userSectionIds)
            ->firstOrFail();

        $this->call("post", "sections/{$section->id}/buy", ["stripe_token" => "tok_visa"]);

        $this->assertHttp200Success();
        $this->assertLogged("user_payment_succeed_for_section", $this->user);
        $this->assertInDatabase("purchases", [
            "user_id" => $this->user->id,
            "purchasable_id" => $section->id,
            "purchasable_type" => "section"
        ]);
    }


    /**
     * POST /sections/{id}/buy
     */
    public function testBuyAlreadyPurchased()
    {
        // Skip for moderators and admins
        if ($this->user->type != "registered") {
            return;
        }

        $userSectionIds = $this->user->purchases()
            ->where("purchasable_type", "=", "section")
            ->get()
            ->pluck("purchasable_id")
            ->toArray();
        $section = Section::whereVisible(true)
            ->whereIn("id", $userSectionIds)
            ->firstOrFail();

        $this->call("post", "sections/{$section->id}/buy", ["stripe_token" => "tok_visa"]);

        $this->assertHttp400InvalidRequest();
    }


    /**
     * POST /sections/{id}/buy
     */
    public function testBuyInvalidToken()
    {
        // Skip for moderators and admins
        if ($this->user->type != "registered") {
            return;
        }

        $userSectionIds = $this->user->purchases()
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
            "user_id" => $this->user->id,
            "purchasable_id" => $section->id,
            "purchasable_type" => "section"
        ]);
    }
}
