<?php

namespace App\Http\Controllers;

use App\Models\BaseModel;
use App\Models\Vote;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class VoteController
 *
 * @author Mathrix Back-End Team
 * @package App\Http\Controllers
 * @since 0.9.1
 * @copyright Mathrix
 */
class VoteController extends BaseController
{
    /**
     * @apiVersion 0.9.1
     * @api {post} /votes/{votable_type}/{votable_id}/up Up
     * @apiGroup Votes
     * @apiName VoteUp
     * @apiDescription Up vote something
     * @apiPermission registered
     * @apiUse Bearer
     * @apiUse 401Unauthorized
     * @apiUse 422Save
     *
     * @param Request $request
     * @param string $votable_type
     * @param int $votable_id
     *
     * @return JsonResponse
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
     */
    private function vote(Request $request, string $votable_type, int $votable_id, int $value)
    {
        // Ensure that the type is morphed in the morphMap
        /** @var BaseModel $class */
        if ($class = Relation::getMorphedModel($votable_type) == null) {
            return $this->http400InvalidRequest();
        }

        if ($request->user()->cant('vote', Vote::class)) {
            return $this->http401Unauthorized();
        }

        $voteData = [
            'user_id' => $request->user()->id,
            'votable_id' => $votable_id,
            'votable_type' => $votable_type
        ];

        $voteConditions = [];
        foreach ($voteData as $key => $v) {
            $voteConditions[] = [$key, '=', $v];
        }


        if (!Vote::where($voteConditions)->exists()) {
            $vote = new Vote($voteData);
            $vote->value = $value;

            if (!$vote->save()) {
                return $vote->handleResponse($this->http422Save());
            }
        } else {
            /** @var Vote $vote */
            $vote = Vote::where($voteConditions)->first();

            if ($vote->value == $value) {
                if (!$vote->delete()) {
                    return $vote->handleResponse($this->http422Save());
                }
            } else {
                if (!$vote->update(['value' => $value])) {
                    return $vote->handleResponse($this->http422Delete());
                }
            }
        }

        return $this->http200Success($vote->exists());
    }


    /**
     * @apiVersion 0.9.1
     * @api {post} /votes/{votable_type}/{votable_id}/down Down
     * @apiGroup Votes
     * @apiName VoteDown
     * @apiDescription Down vote something
     * @apiPermission registered
     * @apiUse Bearer
     * @apiUse 401Unauthorized
     * @apiUse 422Save
     *
     * @param Request $request
     * @param string $votable_type
     * @param int $votable_id
     *
     * @return JsonResponse
     */
    public function down(Request $request, string $votable_type, int $votable_id)
    {
        return $this->vote($request, $votable_type, $votable_id, -1);
    }
}