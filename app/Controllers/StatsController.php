<?php

namespace App\Controllers;

use App\Exceptions\Http400InvalidRequestException;
use App\Exceptions\Http401UnauthorizedException;
use App\Models\Log;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Class StatsController.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     1.0.0
 * @copyright Mathrix Education SA
 * @package   App\Controllers
 */
class StatsController extends BaseController
{
    /**
     * @apiVersion     4.0.0
     *
     * @api            {post} /stats/activities Activities
     * @apiGroup       Stats
     * @apiName        StatsActivities
     * @apiDescription Get statistics of activities completed by user
     * @apiPermission  registered for self get, at least admin for strangers
     * @apiParam (Data) {int} [user_id] The user id (only use to get another user stats)
     * @apiParam (Data) {string=day,week,month,year} [interval=day] The interval unit
     * @apiParam (Data) {string} [start] The interval start (format Y-m-d)
     * @apiParam (Data) {string} [end] The interval end (format Y-m-d)
     * @apiSuccess {string[]} stats Array of stats, key is the date
     * @apiSuccess {int[][]} stats.points The points stats
     * @apiSuccess {int} stats.points.total The total of points gathered during the given interval
     * @apiSuccess {int[]} stats.points.details The detailed points gathered all activities types
     * @apiSuccess {int} stats.points.details.course The detailed points gathered for course activities
     * @apiSuccess {int} stats.points.details.exercise The detailed points gathered for exercise activities
     * @apiSuccess {int} stats.points.details.flashcard The detailed points gathered for flashcard activities
     * @apiSuccess {int} stats.points.details.summary The detailed points gathered for summary activities
     * @apiSuccess {int} stats.points.details.test The detailed points gathered for test activities
     * @apiSuccess {int[][]} stats.counts The activities completed count stats
     * @apiSuccess {int} stats.counts.total The total count of activities completed during the current interval
     * @apiSuccess {int[]} stats.counts.details The detailed count of activities completed during the current interval
     * @apiSuccess {int} stats.counts.details.course The detailed course completed activities count
     * @apiSuccess {int} stats.counts.details.exercise The detailed exercise completed activities count
     * @apiSuccess {int} stats.counts.details.flashcard The detailed flashcard completed activities count
     * @apiSuccess {int} stats.counts.details.summary The detailed summary completed activities count
     * @apiSuccess {int} stats.counts.details.test The detailed test completed activities count
     * @apiUse         Http401Unauthorized
     * @apiUse         Http422ModelSave
     *
     * @param Request $request The request
     *
     * @throws Http400InvalidRequestException
     * @throws Http401UnauthorizedException
     * @throws ValidationException
     *
     * @return JsonResponse
     */
    public function activities(Request $request)
    {
        $user = $this->getUserByRequestOrUnauthorized($request);

        $rules = [
            "interval" => "in:day,week,month,year",
            "start" => "date_format:Y-m-d",
            "end" => "date_format:Y-m-d",
        ];

        $this->validate($request, $rules);

        $interval = $request->input("interval", "day");
        $start = $request->input("start");
        $end = $request->input("end");

        $statsRequest = Log::whereUserId($user->id)
            ->whereEvent("user_gathered_points");

        if (!empty($start)) {
            $start = Carbon::createFromFormat("Y-m-d", $start);
            $statsRequest = $statsRequest->whereDate("created_at", ">", $start);
        }

        if (!empty($end)) {
            $end = Carbon::createFromFormat("Y-m-d", $end);
            $statsRequest = $statsRequest->whereDate("created_at", "<", $end);
        }

        $statsRaw = $statsRequest->where("loggable_type", "module")
            ->with(["loggable.activities"])
            ->get(["value", "loggable_type", "loggable_id", "created_at"]);

        $stats = [];

        if ($statsRaw->isEmpty()) {
            return response()->json($stats);
        }
        /** @var Carbon $dateIterator */
        $dateIterator = $statsRaw->first()->created_at;

        foreach ($statsRaw as $statRaw) {
            /** @var Log $statRaw */
            $created = $statRaw->created_at;

            switch ($interval) {
                case "day":
                    $dateIterator = $created;
                    break;
                case "week":
                    $potentialIterator = $dateIterator->copy()
                        ->addWeek();
                    $dateIterator = $created->lessThan($potentialIterator) ? $dateIterator : $potentialIterator;
                    break;
                case "month":
                    $potentialIterator = $dateIterator->copy()
                        ->addMonth();
                    $dateIterator = $created->lessThan($potentialIterator) ? $dateIterator : $potentialIterator;
                    break;
                case "year":
                    $potentialIterator = $dateIterator->copy()
                        ->addYear();
                    $dateIterator = $created->lessThan($potentialIterator) ? $dateIterator : $potentialIterator;
                    break;
            }

            $key = $dateIterator->toDateString();
            $points = $statRaw->value["points"];
            $type = $statRaw->loggable->activities->first()->type;

            if (empty($stats[$key])) {
                $stats[$key] = [
                    "points" => [
                        "total" => $points,
                        "details" => [
                            $type => $points,
                        ],
                    ],
                    "counts" => [
                        "total" => 1,
                        "details" => [
                            $type => 1,
                        ],
                    ],
                ];
            } else if (empty($stats[$key]["points"]["details"][$type])) {
                $stats[$key]["points"]["total"] += $points;
                $stats[$key]["points"]["details"][$type] = $points;

                ++$stats[$key]["counts"]["total"];
                $stats[$key]["counts"]["details"][$type] = 1;
            } else {
                $stats[$key]["points"]["total"] += $points;
                $stats[$key]["points"]["details"][$type] += $points;

                ++$stats[$key]["counts"]["total"];
                ++$stats[$key]["counts"]["details"][$type];
            }
        }

        return response()->json($stats);
    }
}
