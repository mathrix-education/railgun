<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\User;
use App\Models\Vote;
use Exception;
use Illuminate\Http\Request;
use Mathrix\Lumen\Zero\Controllers\BaseController;
use Mathrix\Lumen\Zero\Exceptions\ValidationException;
use Mathrix\Lumen\Zero\Responses\SuccessJsonResponse;

/**
 * Serve routes starting with /votes.
 */
class VotesController extends BaseController
{
    /**
     * POST /votes/comment/{commentId}
     *
     * @param Request $request   The Illuminate HTTP request.
     * @param int     $commentId The comment id.
     *
     * @return SuccessJsonResponse The newly created vote.
     *
     * @throws ValidationException
     */
    public function vote(Request $request, int $commentId) : SuccessJsonResponse
    {
        $this->validate($request, ['value' => 'required|integer|in:-1,1']);

        /** @var User $user */
        $user = $request->user();

        /** @var Vote $vote */
        $vote = Vote::query()
            ->where('user_id', '=', $user->id)
            ->where('comment_id', '=', $commentId)
            ->firstOrNew([
                'user_id'    => $user->id,
                'comment_id' => $commentId,
            ]);

        $vote->value = $request->json('value');
        $vote->save();

        return new SuccessJsonResponse($vote);
    }

    /**
     * DELETE /votes/comment/{commentId}
     *
     * @param Request $request   The Illuminate HTTP request.
     * @param int     $commentId The comment id.
     *
     * @return SuccessJsonResponse The success response.
     *
     * @throws Exception
     */
    public function unVote(Request $request, int $commentId) : SuccessJsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $vote = Vote::query()
            ->where('user_id', '=', $user->id)
            ->where('comment_id', '=', $commentId)
            ->firstOrFail();

        $vote->delete();

        return new SuccessJsonResponse([], [
            'success' => true,
            'message' => "Vote associated to comment id $commentId was successfully deleted.",
        ]);
    }
}
