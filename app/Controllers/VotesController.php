<?php

namespace App\Controllers;

use App\Models\Vote;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Mathrix\Lumen\Bases\BaseController;
use Mathrix\Lumen\Bases\BaseModel;
use Mathrix\Lumen\Exceptions\Http\Http400BadRequestException;

/**
 * Class VotesController.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 1.0.0
 */
class VotesController extends BaseController
{
    /**
     * Add a vote in the database.
     *
     * @param Request $request The Illuminate HTTP request.
     * @param string $votableType The votable type
     * @param int $votableId The votable id.
     * @param int $value The vote value (+/-1)
     *
     * @return JsonResponse
     * @throws Http400BadRequestException
     * @throws \Mathrix\Lumen\Exceptions\ValidationException
     * @throws \Exception
     */
    private function vote(Request $request, string $votableType, int $votableId, int $value)
    {
        // Ensure that the type is morphed in the morphMap
        /** @var BaseModel $modelClass */
        if ($modelClass = Relation::getMorphedModel($votableType) === null) {
            throw new Http400BadRequestException();
        }

        $voteData = [
            "user_id" => $request->user()->id,
            "votable_id" => $votableId,
            "votable_type" => $votableType,
        ];

        $voteConditions = [];
        foreach ($voteData as $k => $v) {
            $voteConditions[] = [$k, "=", $v];
        }

        /** @var Vote $vote */
        $vote = Vote::query()
            ->where($voteConditions)
            ->firstOrNew($$voteData);

        if (!$vote->exists) {
            $vote->save();
        } else {
            if ($vote->value === $value) {
                $vote->delete();
            } else {
                $vote->update(["value" => $value]);
            }
        }

        return new JsonResponse($vote->votable); // TODO: This not REST
    }


    /**
     * POST /votes/{votable_type}/{votable_id}/up
     * Up vote a model.
     *
     * @param Request $request The Illuminate HTTP request.
     * @param string $votableType The votable type.
     * @param int $votableId The votable id
     *
     * @return JsonResponse
     * @throws Http400BadRequestException
     * @throws \Mathrix\Lumen\Exceptions\ValidationException
     */
    public function up(Request $request, string $votableType, int $votableId)
    {
        return $this->vote($request, $votableType, $votableId, 1);
    }


    /**
     * POST /votes/{votable_type}/{votable_id}/down
     * Down vote a model.
     *
     * @param Request $request The Illuminate HTTP request.
     * @param string $votableType The votable type.
     * @param int $votableId The votable id
     *
     * @return JsonResponse
     * @throws Http400BadRequestException
     * @throws \Mathrix\Lumen\Exceptions\ValidationException
     */
    public function down(Request $request, string $votableType, int $votableId)
    {
        return $this->vote($request, $votableType, $votableId, -1);
    }
}
