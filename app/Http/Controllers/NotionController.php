<?php

namespace App\Http\Controllers;


use App\Models\Chapter;
use App\Models\Notion;
use Illuminate\Http\JsonResponse;

/**
 * Class NotionController
 * @author Mathrix Team
 * @package App\Http\Controllers
 */
class NotionController extends BaseController
{
    /**
     * Get notions associated with a chapter id
     * @param int $chapterId
     * @return JsonResponse
     */
    public function index(int $chapterId)
    {
        $notions = Notion::whereChapterId($chapterId)->get();

        if ($notions->isEmpty()) {
            $chapter = Chapter::whereId($chapterId);

            if (!$chapter instanceof Chapter) {
                return response()->json([
                    'error' => 'not_found',
                    'message' => 'Chapter with id ' . $chapterId . ' was not found'
                ], 404);
            }
        }

        return response()->json($notions);
    }
}