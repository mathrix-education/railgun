<?php
namespace Tests\Stats;

use Carbon\Carbon;

/**
 * Class StatsAsRegisteredTest
 * Test stats as registered user
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 * @version 1.0.0
 * @package Tests\Section
 */
class StatsAsRegisteredTest extends StatsAsPublicTest
{
    /**
     * POST /stats/activities
     * Assert that stats are coherent
     *
     * @throws \Exception
     */
    public function testActivities()
    {
        $this->call("post", "/stats/activities");

        $data = $this->getResponseData();

        foreach ($data as $stat) {
            $totalPoints = 0;
            $totalCount = 0;

            foreach ($stat->points->details as $value) {
                $totalPoints += $value;
            }
            foreach ($stat->counts->details as $value) {
                $totalCount += $value;
            }

            $this->assertEquals($totalPoints, $stat->points->total);
            $this->assertEquals($totalCount, $stat->counts->total);
        }
    }


    /**
     * POST /stats/activities
     * Assert that stats are coherent and limits are respected
     *
     * @throws \Exception
     */
    public function testActivitiesWithIntervalLimits()
    {
        $start = Carbon::now()->subDays(25);
        $end = Carbon::now()->subDays(2);

        $this->call("post", "/stats/activities", [
            "start" => $start->toDateString(),
            "end" => $end->toDateString()
        ]);

        // Increase interval limits
        $start->subDay();
        $end->addDay();

        $data = $this->getResponseData();

        foreach ($data as $date => $stat) {
            $carbonDate = Carbon::createFromFormat("Y-m-d", $date);
            $totalPoints = 0;
            $totalCount = 0;

            foreach ($stat->points->details as $value) {
                $totalPoints += $value;
            }
            foreach ($stat->counts->details as $value) {
                $totalCount += $value;
            }

            $this->assertEquals($totalPoints, $stat->points->total);
            $this->assertEquals($totalCount, $stat->counts->total);
            $this->assertTrue($carbonDate->greaterThan($start));
            $this->assertTrue($carbonDate->lessThan($end));
        }
    }


    /**
     * POST /stats/activities
     * Assert that stats are coherent and interval size is respected
     *
     * @throws \Exception
     */
    public function testActivitiesWithIntervalSize()
    {
        $this->call("post", "/stats/activities", [
            "interval" => "week"
        ]);

        $data = $this->getResponseData();

        /** @var Carbon|null $previousCarbonDate */
        $previousCarbonDate = null;

        foreach ($data as $date => $stat) {
            $carbonDate = Carbon::createFromFormat("Y-m-d", $date);
            $totalPoints = 0;
            $totalCount = 0;

            foreach ($stat->points->details as $value) {
                $totalPoints += $value;
            }
            foreach ($stat->counts->details as $value) {
                $totalCount += $value;
            }

            $this->assertEquals($totalPoints, $stat->points->total);
            $this->assertEquals($totalCount, $stat->counts->total);

            if ($previousCarbonDate !== null) {
                $this->assertEquals($previousCarbonDate->copy()->addWeek()->toDateString(), $carbonDate->toDateString());
            }
            $previousCarbonDate = $carbonDate;
        }
    }


    /**
     * POST /stats/activities as stranger -> HTTP 401 unauthorized
     *
     * @throws \Exception
     */
    public function testActivitiesForStranger()
    {
        $this->call("post", "/stats/activities", [
            "user_id" => $this->stranger()->id
        ]);

        $this->assertHttp401Unauthorized();
    }
}
