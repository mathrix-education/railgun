<?php
namespace App\Http\Controllers;


use App\Models\Chapter;
use App\Models\Class_;
use App\Models\Country;
use App\Models\Discipline;
use Illuminate\Http\JsonResponse;

/**
 * Class ChapterController
 * @package App\Http\Controllers
 */
class ChapterController extends BaseController
{
    /**
     * Retrieve all chapters based on country, class and discipline
     *
     * @param Country $country
     * @param string $classShortSlug
     * @param Discipline $discipline
     *
     * @return JsonResponse
     */
    public function index(Country $country, string $classShortSlug, Discipline $discipline) {
        $class = $country->classes->where('slug_short',  $classShortSlug)->first();

        if($class instanceof Class_) {
            $chapters = Chapter::whereClassId($class->id)
                ->whereDisciplineId($discipline->id)
                ->get();

            return response()->json($chapters);
        } else {
            return response()->json([
                'error' => 'not_found',
                'message' => 'Cannot found this class for this country'
            ], 404);
        }
    }
}