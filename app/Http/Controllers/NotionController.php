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
     * Retrieve notions based on chapter
     *
     * @param Chapter $chapter
     *
     * @return JsonResponse
     */
    public function index(Chapter $chapter)
    {
        return response()->json($chapter->notions);
    }
}