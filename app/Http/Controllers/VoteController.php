<?php
namespace App\Http\Controllers;

use App\Models\Vote;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Mathrix\Lumen\Http\Controllers\BaseController;
use Mathrix\Lumen\Models\BaseModel;

class VoteController extends BaseController
{
    private function vote(Request $request, string $type, int $id, int $value)
    {
        // Ensure that the type is morphed in the morphMap
        /** @var BaseModel $class */
        if ($class = Relation::getMorphedModel($type) == null) {
            return $this->http400InvalidRequest();
        }

        if (!$request->user()->cant('vote', Vote::class)) {
            return $this->http401Unauthorized();
        }

        $voteData = $voteStrictConditions = [
            'value' => $value,
            'user_id' => $request->input('user_id') ?? $request->user()->id,
            'votable_id' => $id,
            'votable_type' => $type
        ];
        array_walk($voteStrictConditions, function ($key, $value) {
            return [$key, '=', $value];
        });
        $voteSoftConditions = $voteStrictConditions;
        array_shift($voteSoftConditions);

        if (Vote::where($voteStrictConditions)->exists()) {
            return $this->http409Conflict();
        } elseif (Vote::where($voteSoftConditions)->exists()) {
            $vote = Vote::where($voteSoftConditions)->first();
            $vote->fill($voteData);
        } else {
            $vote = new Vote($voteData);
        }

        // Check permission
        if ($request->user()->cant('add', $vote)) {
            return $this->http401Unauthorized();
        }

        // Check save state
        if (!$vote->save()) {
            return $vote->handleResponse($this->http422Save());
        }

        return $this->http200Success($vote);
    }


    /**
     * @apiVersion 1.0.0
     * @api {post} /classes Add
     * @apiGroup Classes
     * @apiName ClassAdd
     * @apiDescription Add class
     * @apiPermission admin
     * @apiUse Bearer
     * @apiUse ClassData
     * @apiUse Class
     * @apiUse 401Unauthorized
     * @apiUse 422Validation
     * @apiUse 422Save
     *
     * @param Request $request
     * @param string $type
     * @param int $id
     *
     * @return JsonResponse
     */
    public function up(Request $request, string $type, int $id)
    {
        return $this->vote($request, $type, $id, 1);
    }
}