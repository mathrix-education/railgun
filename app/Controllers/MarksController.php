<?php

namespace App\Controllers;

use App\Models\Activity;
use App\Models\Chapter;
use App\Models\Country;
use App\Models\Discipline;
use App\Models\Grade;
use App\Models\Mark;
use App\Models\Notion;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Mathrix\Lumen\Bases\BaseController;

/**
 * Class MarksController.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 */
class MarksController extends BaseController
{
    /**
     * Convert a collect of Marks to a collection of Tree
     *
     * @param Collection|Mark[] $marks
     *
     * @return Collection
     */
    private function getTreeFromMarks($marks)
    {
        return $marks->map(function (Mark $mark) {
            /** @var Activity $activity */
            $activity = $mark->markable;

            /** @var Notion $notion */
            $notion = $activity->notion()->firstOrFail();
            /** @var Chapter $chapter */
            $chapter = $notion->chapter()->firstOrFail();
            /** @var Grade $grade */
            $grade = $chapter->grade()->firstOrFail();
            /** @var Discipline $grade */
            $discipline = $chapter->discipline()->firstOrFail();
            /** @var Country $country */
            $country = $grade->country()->firstOrFail();

            return [
                "country" => $country,
                "discipline" => $discipline,
                "grade" => $grade,
                "chapter" => $chapter,
                "notion" => $notion,
                "activity" => $activity
            ];
        });
    }


    /**
     * GET /marks/activities/to-revise
     * Get the activities marked as "to_revise" for the current user.
     *
     * @param Request $request The Illuminate HTTP request.
     *
     * @return JsonResponse
     */
    public function activitiesToRevise(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $marks = $user->marks()
            ->with("markable")
            ->where([
                ["status", "=", "to_revise"],
                ["markable_type", "=", "activity"]
            ])
            ->get();

        $trees = $this->getTreeFromMarks($marks);

        return new JsonResponse($trees);
    }
}
