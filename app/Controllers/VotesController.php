<?php

namespace App\Controllers;

use App\Models\User;
use App\Models\Vote;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Mathrix\Lumen\Bases\BaseController;
use Mathrix\Lumen\Exceptions\ValidationException;

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
     * POST /votes/comment/{commentId}
     *
     * @param Request $request The Illuminate HTTP request.
     * @param int $commentId The comment id.
     *
     * @return JsonResponse
     * @throws ValidationException
     */
    public function vote(Request $request, int $commentId): JsonResponse
    {
        $this->validate($request, [
            "value" => "required|integer|in:-1,1"
        ]);

        /** @var User $user */
        $user = $request->user();

        /** @var Vote $vote */
        $vote = Vote::query()
            ->where("user_id", "=", $user->id)
            ->where("comment_id", "=", $commentId)
            ->firstOrNew([
                "user_id" => $user->id,
                "comment_id" => $commentId
            ]);

        $vote->value = $request->json("value");
        $vote->save();

        return new JsonResponse($vote);
    }


    /**
     * DELETE /votes/comment/{commentId}
     *
     * @param Request $request The Illuminate HTTP request.
     * @param int $commentId The comment id.
     *
     * @return JsonResponse
     * @throws Exception
     */
    public function unVote(Request $request, int $commentId): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $vote = Vote::query()
            ->where("user_id", "=", $user->id)
            ->where("comment_id", "=", $commentId)
            ->firstOrFail();

        $vote->delete();

        return new JsonResponse([
            "success" => true,
            "message" => "Vote associated to comment id $commentId was successfully deleted.",
        ]);
    }
}
