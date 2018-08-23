<?php

namespace Tests\Unit;

use App\Services\Braintree\Models\Plan;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Tests\BaseTestCase;

/**
 * Class PlanTest.
 *
 * @author Mathieu Bour <mathieu.tin.bour@ŋmail.com>
 *
 * @since 4.1.0
 *
 * @copyright Mathrix Education SA
 */
class PlanTest extends BaseTestCase
{
    public function testAll()
    {
        $plans = Plan::all();
        $this->assertInstanceOf(Collection::class, $plans);
        foreach ($plans as $plan) {
            $this->assertInstanceOf(Plan::class, $plan);
        }
    }

    /**
     * @return Plan|null
     */
    public function testFind()
    {
        $plan = Plan::find("v4_annually");
        $this->assertInstanceOf(Plan::class, $plan);
        $nonExistingPlan = Plan::find("non_existing_plan");
        $this->assertNull($nonExistingPlan);

        return $plan;
    }

    /**
     * @expectedException \App\Exceptions\Http404ModelNotFoundException
     */
    public function testFindOrFail()
    {
        $plan = Plan::findOrFail("v4_monthly");
        $this->assertInstanceOf(Plan::class, $plan);
        Plan::findOrFail("non_existing_plan");
    }

    /**
     * @depends testFind
     *
     * @param Plan $plan
     */
    public function testToArray(Plan $plan)
    {
        $planArray = $plan->toArray();
        $this->assertArrayHasKey("id", $planArray);
        $this->assertArrayHasKey("pricePerMonth", $planArray);
    }

    /**
     * @depends testFind
     *
     * @param Plan $plan
     */
    public function testGetTrialEndDate(Plan $plan)
    {
        // Test before 1st May
        $referenceBefore1stMay = new Carbon("2018-04-31 12:00:00");
        Carbon::setTestNow($referenceBefore1stMay);
        $trialEndDate = $plan->getTrialEndDate();
        $this->assertEquals($trialEndDate->format("Y-m-d"), "2018-07-31");

        // Test after 1st May
        $referenceAfter1stMay = new Carbon("2018-05-02 12:00:00");
        Carbon::setTestNow($referenceAfter1stMay);
        $trialEndDate = $plan->getTrialEndDate();
        $this->assertEquals($trialEndDate->format("Y-m-d"), "2019-07-31");
    }
}
