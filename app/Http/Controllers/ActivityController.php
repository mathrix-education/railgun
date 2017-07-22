<?php

namespace App\Http\Controllers;

use App\Models\Notion;
use Illuminate\Http\JsonResponse;

/**
 * Class ActivityController
 * @author Mathrix Team
 * @package App\Http\Controllers
 */
class ActivityController extends BaseController
{
    /**
     * @param int $notionId
     * @return JsonResponse
     */
    public function index(int $notionId)
    {
        $notion = Notion::whereId($notionId)->with('activities')->first();

        if (!$notion instanceof Notion) {
            return response()->json([
                'error' => 'not_found',
                'message' => 'Notion with id ' . $notionId . ' was not found'
            ]);
        }

        $activities = $notion->activities;

        return response()->json($activities);
    }
}