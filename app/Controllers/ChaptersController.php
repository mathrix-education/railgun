<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Chapter;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Mathrix\Lumen\Zero\Controllers\BaseController;
use Mathrix\Lumen\Zero\Responses\SuccessJsonResponse;

/**
 * Serve routes starting with /chapters.
 */
class ChaptersController extends BaseController
{
    /**
     * GET /chapters/{chapterId}/recursive
     * Get a chapter and all its visible children (notions, activities, modules).
     *
     * @param Request $request   The Illuminate HTTP request.
     * @param int     $chapterId The chapter id
     *
     * @return SuccessJsonResponse The chapter, and its lineage (notions, activities, modules).
     */
    public function recursive(Request $request, int $chapterId) : SuccessJsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($user === null) {
            $prefix = 'user.0'; // id = 0 means not logged
        } else {
            $prefix = "user.{$user->id}";
        }

        $key = "$prefix.chapter.$chapterId.recursive";

        /** @var array $chapterData */
        $chapterData = Cache::remember($key, 3600, static function () use ($chapterId) {
            return Chapter::recursive($chapterId)
                          ->toArray();
        });

        return new SuccessJsonResponse($chapterData);
    }
}
