<?php

namespace App\Http\Controllers;


use App\Models\Chapter;
use App\Models\Class_;
use App\Models\Country;
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
     * Retrieve all chapters based on country, class and discipline
     *
     * @param string $countrySlug
     * @param string $classShortSlug
     * @param string $disciplineSlug
     *
     * @return JsonResponse
     */
    public function index(string $countrySlug, string $classShortSlug, string $disciplineSlug)
    {
        // Retrieve country by slug
        $country = Country::whereSlugShort($countrySlug)->first();

        if (!$country instanceof Country) {
            return response()->json([
                'error' => 'not_found',
                'message' => 'Country with slug ' . $countrySlug . ' was not found'
            ], 404);
        }

        $discipline = Discipline::whereSlug($disciplineSlug)->first();

        // Retrieve discipline by slug
        if (!$country instanceof Country) {
            return response()->json([
                'error' => 'not_found',
                'message' => 'Discipline with slug ' . $disciplineSlug . ' was not found'
            ], 404);
        }

        // Retrieve class by short slug and country
        $class = $country->classes->where('slug_short', $classShortSlug)->first();

        if ($class instanceof Class_) {
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