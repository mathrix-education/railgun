<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Activity;
use App\Models\Mark;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Mathrix\Lumen\Zero\Controllers\BaseController;
use Mathrix\Lumen\Zero\Responses\SuccessJsonResponse;

/**
 * Serve routes starting by /activities.
 */
class ActivitiesController extends BaseController
{
    /**
     * PATCH /activities/{activityId}/mark
     * Mark an activity.
     *
     * @param Request $request The Illuminate HTTP request.
     * @param int     $id      The activity id.
     *
     * @return SuccessJsonResponse The marked activity.
     */
    public function mark(Request $request, int $id) : SuccessJsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        /** @var Activity $activity */
        $activity = Activity::query()
                            ->findOrFail($id);
        $status   = $request->json('status');

        $mark         = Mark::retrieve($activity, $user);
        $mark->status = $status;
        $mark->save();

        $cacheKey = "user.{$user->id}.chapter.{$activity->notion->chapter->id}.recursive";
        DB::table('cache')
          ->where('key', '=', $cacheKey)
          ->delete();

        return new SuccessJsonResponse($activity->fresh());
    }

    /**
     * PATCH /activities/{activityId}/unmark
     * Unmark an activity.
     *
     * @param Request $request The Illuminate HTTP request.
     * @param int     $id      The activity id.
     *
     * @return SuccessJsonResponse The un-marked activity.
     */
    public function unmark(Request $request, int $id) : SuccessJsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        /** @var Activity $activity */
        $activity = Activity::query()
                            ->findOrFail($id);

        Mark::retrieveAll($activity, $user)
            ->each(
                static function (Mark $mark) : void {
                    $mark->delete();
                }
            );

        $cacheKey = "user.{$user->id}.chapter.{$activity->notion->chapter->id}.recursive";
        DB::table('cache')
          ->where('key', '=', $cacheKey)
          ->delete();

        return new SuccessJsonResponse($activity->fresh());
    }
}
