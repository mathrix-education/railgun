<?php
namespace App\Http\Controllers;

use App\Models\Chapter;
use Illuminate\Http\JsonResponse;
use Mathrix\Lumen\Http\Controllers\BaseController;

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
    public function index(Chapter $chapter): JsonResponse
    {
        return response()->json($chapter->notions);
    }
}
