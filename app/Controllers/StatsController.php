<?php

namespace App\Controllers;

use App\Exceptions\Http400InvalidRequestException;
use App\Exceptions\Http401UnauthorizedException;
use App\Models\Log;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Mathrix\Lumen\Bases\BaseController;

/**
 * Class StatsController.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 1.0.0
 */
class StatsController extends BaseController
{
    /**
     * GET /stats/activities
     *
     * @param Request $request The Illuminate HTTP request.
     *
     * @return JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function activities(Request $request)
    {
        $user = $request->user();

        $this->validate($request, [
            "interval" => "in:day,week,month,year",
            "start" => "date_format:Y-m-d",
            "end" => "date_format:Y-m-d",
        ]);

        $interval = $request->input("interval", "day");
        $start = $request->input("start");
        $end = $request->input("end");

        $query = Log::query()
            ->where([
                ["user_id", "=", $user->id],
                ["event", "=", "user_gathered_points"]
            ]);

        if (!empty($start)) {
            $start = Carbon::createFromFormat("Y-m-d", $start);
            $query = $query->whereDate("created_at", ">", $start);
        }

        if (!empty($end)) {
            $end = Carbon::createFromFormat("Y-m-d", $end);
            $query = $query->whereDate("created_at", "<", $end);
        }

        $statsRaw = $query->where("loggable_type", "module")
            ->with(["loggable.activities"])
            ->get(["value", "loggable_type", "loggable_id", "created_at"]);

        $stats = [];

        if ($statsRaw->isEmpty()) {
            return new JsonResponse($stats);
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

                $stats[$key]["counts"]["total"]++;
                $stats[$key]["counts"]["details"][$type] = 1;
            } else {
                $stats[$key]["points"]["total"] += $points;
                $stats[$key]["points"]["details"][$type] += $points;

                $stats[$key]["counts"]["total"]++;
                $stats[$key]["counts"]["details"][$type]++;
            }
        }

        return new JsonResponse($stats);
    }
}
