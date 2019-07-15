<?php

namespace App\Controllers;

use App\Models\Chapter;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Mathrix\Lumen\Zero\Controllers\BaseController;
use Mathrix\Lumen\Zero\Responses\SuccessJsonResponse;

/**
 * Class ChaptersController.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 1.0.0
 */
class ChaptersController extends BaseController
{
    /**
     * GET /chapters/{chapterId}/recursive
     * Get a chapter and all its visible children (notions, activities, modules).
     *
     * @param Request $request
     * @param int $chapterId The chapter id
     *
     * @return SuccessJsonResponse
     */
    public function recursive(Request $request, int $chapterId): SuccessJsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($user === null) {
            return new SuccessJsonResponse(Chapter::recursive($chapterId));
        }

        /** @var array $chapterData */
        $chapterData = Cache::remember(
            "user.{$user->id}.chapter.{$chapterId}.recursive",
            60,
            function () use ($chapterId) {
                return Chapter::recursive($chapterId)->toArray();
            });

        return new SuccessJsonResponse($chapterData);
    }
}
