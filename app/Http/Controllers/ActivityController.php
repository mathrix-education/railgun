<?php
namespace App\Http\Controllers;

use App\Models\Notion;
use Illuminate\Http\JsonResponse;
use Mathrix\Lumen\Http\Controllers\BaseController;

/**
 * Class ActivityController
 * @author Mathrix Team
 * @package App\Http\Controllers
 */
class ActivityController extends BaseController
{
    /**
     * Retrieve activities based on a notion
     *
     * @param Notion $notion
     *
     * @return JsonResponse
     */
    public function index(Notion $notion): JsonResponse
    {
        return response()->json($notion->activities);
    }
}