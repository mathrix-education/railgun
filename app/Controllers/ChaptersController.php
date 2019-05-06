<?php

namespace App\Controllers;

use App\Models\Chapter;
use App\Models\Grade;
use App\Models\Module;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Mathrix\Lumen\Bases\BaseController;
use Mathrix\Lumen\Exceptions\Models\Models\ModelNotFoundException;

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
     * @return JsonResponse
     */
    public function recursive(Request $request, int $chapterId)
    {
        /** @var User $user */
        $user = $request->user();

        if ($user === null) {
            return new JsonResponse(Chapter::recursive($chapterId));
        }

        /** @var array $chapterData */
        $chapterData = Cache::remember(
            "user.{$user->id}.chapter.{$chapterId}.recursive",
            60,
            function () use ($chapterId) {
                return Chapter::recursive($chapterId)->toArray();
            });

        return new JsonResponse($chapterData);
    }
}
