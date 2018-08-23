<?php

namespace App\Controllers;

use App\Exceptions\Http400InvalidRequestException;
use App\Exceptions\Http401UnauthorizedException;
use App\Exceptions\Http404ModelNotFoundException;
use App\Exceptions\Http422ModelSaveException;
use App\Exceptions\Http422ModelValidationException;
use App\Models\Activity;
use App\Models\Discipline;
use App\Models\Meta;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class ActivityController.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 *
 * @since     1.0.0
 *
 * @copyright Mathrix Education SA
 */
class ActivityController extends BaseController
{
    /**
     * @param Request $request
     *
     * @throws Http400InvalidRequestException
     * @throws Http401UnauthorizedException
     *
     * @return JsonResponse
     */
    public function favorites(Request $request): JsonResponse
    {
        $user = $this->getUserByRequestOrUnauthorized($request);
        $metas = Meta::with(["country", "class_", "discipline", "chapter", "notion", "activity", "activity.modules"])
            ->where([
                ["user_id", $user->id],
                ["favorite", true],
            ])
            ->get();

        return response()->json($metas);
    }

    /**
     * @param Request $request
     *
     * @throws Http400InvalidRequestException
     * @throws Http401UnauthorizedException
     *
     * @return JsonResponse
     */
    public function to_revise(Request $request): JsonResponse
    {
        $user = $this->getUserByRequestOrUnauthorized($request);
        $metas = Meta::with(["country", "class_", "discipline", "chapter", "notion", "activity", "activity.modules"])
            ->where([
                ["user_id", $user->id],
                ["to_revise", true],
            ])
            ->get();

        return response()->json($metas);
    }

    /**
     * @param Request $request
     *
     * @throws Http400InvalidRequestException
     * @throws Http401UnauthorizedException
     *
     * @return JsonResponse
     */
    public function incomplete(Request $request): JsonResponse
    {
        $user = $this->getUserByRequestOrUnauthorized($request);
        $disciplinesIds = Discipline::all(["id"])->pluck("id")->toArray();

        /**
         * @var Builder|null
         */
        $query = null;

        foreach ($disciplinesIds as $disciplineId) {
            $subQuery = Meta::with(["country", "class_", "discipline", "chapter", "notion", "activity", "activity.modules"])
                ->where([
                    ["user_id", $user->id],
                    ["completed", false],
                    ["discipline_id", $disciplineId],
                ])
                ->orderBy("updated_at", "desc")
                ->limit(1);

            if ($query === null) {
                $query = $subQuery;
            } else {
                /* @var Builder $query */
                $query->union($subQuery);
            }
        }

        $metas = $query->get();

        return response()->json($metas);
    }

    /**
     * @param Request $request
     * @param int     $id
     *
     * @throws Http400InvalidRequestException
     * @throws Http401UnauthorizedException
     * @throws Http404ModelNotFoundException
     * @throws Http422ModelSaveException
     * @throws Http422ModelValidationException
     *
     * @return JsonResponse
     */
    public function mark(Request $request, int $id): JsonResponse
    {
        $user = $this->getUserByRequestOrUnauthorized($request);

        if (Activity::find($id) === null) {
            throw new Http404ModelNotFoundException();
        }

        $metaData = array_merge(
            ["user_id" => $user->id, "activity_id" => $id],
            $request->only(["country_id", "class_id", "discipline_id", "notion_id", "chapter_id"])
        );

        /** @var Meta $meta */
        $meta = Meta::firstOrNew($metaData);

        $meta->favorite = $request->input("favorite") ?? $meta->favorite ?? false;
        $meta->to_revise = $request->input("to_revise") ?? $meta->to_revise ?? false;
        $meta->completed = $request->input("completed") ?? $meta->completed ?? false;
        $meta->updated_at = Carbon::now();

        $meta->save();

        return response()->json(Activity::find($id));
    }
}
