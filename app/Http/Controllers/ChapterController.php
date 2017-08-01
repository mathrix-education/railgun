<?php
namespace App\Http\Controllers;

use App\Models\Discipline;
use Illuminate\Http\JsonResponse;

/**
 * Class ChapterController
 * @author Mathrix Team
 * @package App\Http\Controllers
 */
class ChapterController extends BaseController
{
    /**
     * Retrieve chapters based on discipline
     *
     * @param Discipline $discipline
     *
     * @return JsonResponse
     */
    public function index(Discipline $discipline): JsonResponse
    {
        return response()->json($discipline->chapters);
    }
}
