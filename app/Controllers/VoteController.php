<?php
namespace App\Controllers;

use App\Exceptions\Http400InvalidRequestException;
use App\Exceptions\Http422ModelSaveException;
use App\Exceptions\Http422ModelValidationException;
use App\Models\BaseModel;
use App\Models\Vote;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class VoteController
 *
 * @author Mathrix Back-End Team
 * @package App\Controllers
 * @since 1.0.0
 * @copyright Mathrix Education SA
 */
class VoteController extends BaseController
{
    /**
     * @apiVersion 4.0.0
     * @api {post} /votes/{votable_type}/{votable_id}/up Up
     * @apiGroup Votes
     * @apiName VoteUp
     * @apiDescription Up vote something
     * @apiPermission registered
     * @apiUse Bearer
     * @apiUse Http401Unauthorized
     * @apiUse Http422ModelSave
     *
     * @param Request $request
     * @param string $votable_type
     * @param int $votable_id
     *
     * @return JsonResponse
     * @throws Http400InvalidRequestException
     * @throws Http422ModelSaveException
     * @throws Http422ModelValidationException
     * @throws \Exception
     */
    public function up(Request $request, string $votable_type, int $votable_id)
    {
        return $this->vote($request, $votable_type, $votable_id, 1);
    }


    /**
     * Handle vote action
     *
     * @param Request $request
     * @param string $votable_type
     * @param int $votable_id
     * @param int $value
     *
     * @return JsonResponse|null
     * @throws Http400InvalidRequestException
     * @throws Http422ModelSaveException
     * @throws Http422ModelValidationException
     * @throws \Exception
     */
    private function vote(Request $request, string $votable_type, int $votable_id, int $value)
    {
        // Ensure that the type is morphed in the morphMap
        /** @var BaseModel $class */
        if ($class = Relation::getMorphedModel($votable_type) == null) {
            throw new Http400InvalidRequestException();
        }

        $voteData = [
            "user_id" => $request->user()->id,
            "votable_id" => $votable_id,
            "votable_type" => $votable_type
        ];

        $voteConditions = [];
        foreach ($voteData as $key => $v) {
            $voteConditions[] = [$key, $v];
        }

        if (!Vote::where($voteConditions)->exists()) {
            $vote = new Vote($voteData);
            $vote->value = $value;

            $vote->save();
        } else {
            /** @var Vote $vote */
            $vote = Vote::where($voteConditions)->first();

            if ($vote->value == $value) {
                $vote->delete();
            } else {
                $vote->update(["value" => $value]);
            }
        }

        return response()->json($vote->exists());
    }


    /**
     * @apiVersion 4.0.0
     * @api {post} /votes/{votable_type}/{votable_id}/down Down
     * @apiGroup Votes
     * @apiName VoteDown
     * @apiDescription Down vote something
     * @apiPermission registered
     * @apiUse Bearer
     * @apiUse Http401Unauthorized
     * @apiUse Http422ModelSave
     *
     * @param Request $request
     * @param string $votable_type
     * @param int $votable_id
     *
     * @return JsonResponse
     * @throws Http400InvalidRequestException
     * @throws Http422ModelSaveException
     * @throws Http422ModelValidationException
     * @throws \Exception
     */
    public function down(Request $request, string $votable_type, int $votable_id)
    {
        return $this->vote($request, $votable_type, $votable_id, -1);
    }
}
