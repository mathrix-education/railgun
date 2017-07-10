<?php
namespace App\Http\Controllers;


use App\Models\Chapter;
use App\Models\Country;
use App\Models\Discipline;

/**
 * Class ChapterController
 * @package App\Http\Controllers
 */
class ChapterController extends BaseController
{
    /**
     * Retrieve all chapters based on country, class and discipline
     * @param Country $country
     * @param string $classShortSlug
     * @param Discipline $discipline
     */
    public function index(Country $country, string $classShortSlug, Discipline $discipline) {
        $class = $country->classes->where('slug_short',  $classShortSlug)->first();

        $chapters = Chapter::whereClassId($class->id)
            ->whereDisciplineId($discipline->id)
            ->get();

        response()->json($chapters);
    }
}