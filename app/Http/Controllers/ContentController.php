<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Chapter;
use App\Models\Class_;
use App\Models\Country;
use App\Models\Notion;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\JsonResponse;
use Mathrix\Lumen\Http\Controllers\BaseController;

/**
 * Class ContentController
 *
 * @author Mathrix Team
 * @package App\Http\Controllers
 * @version 1.0.0
 */
class ContentController extends BaseController
{
    /**
     * @apiVersion 1.0.0
     * @api {get} /content/countries Countries
     * @apiGroup Content
     * @apiName ContentCountries
     * @apiDescription Retrieve content data by class id
     * @apiPermission public
     * @apiUse CountryArray
     * @apiSuccess {Class[]} countries.classes Array of classes
     * @apiSuccess {int} countries.classes.id The class id
     * @apiSuccess {string} countries.classes.name The class name
     * @apiSuccess {string} countries.classes.name_short The class short name
     * @apiSuccess {string} countries.classes.slug The class slug
     * @apiSuccess {string} countries.classes.slug_short The class short slug
     * @apiSuccess {string} countries.classes.country_id The class country id
     * @return JsonResponse
     */
    public function countries()
    {
        $data = Country::with('classes')->get();

        return $this->http200Success($data);
    }


    /**
     * @apiVersion 1.0.0
     * @api {get} /content/program/{classId} Program
     * @apiGroup Content
     * @apiName ContentProgram
     * @apiDescription Retrieve content data by class id
     * @apiPermission public
     * @apiParam {int} id The class id
     * @apiUse Class
     * @apiSuccess {Discipline[]} disciplines The disciplines
     * @apiSuccess {int} disciplines.id The discipline id
     * @apiSuccess {int} disciplines.name The discipline name
     * @apiSuccess {int} disciplines.slug The discipline slug
     * @apiSuccess {Chapter[]} disciplines.chapters The chapters
     * @apiSuccess {int} disciplines.chapters.id The chapter id
     * @apiSuccess {string} disciplines.chapters.name The chapter name
     * @apiSuccess {string} disciplines.chapters.slug The chapter slug
     * @apiSuccess {Notion[]} disciplines.chapters.notions The notions
     * @apiSuccess {int} disciplines.chapters.notions.id The notion id
     * @apiSuccess {string} disciplines.chapters.notions.name The notion name
     * @apiSuccess {string} disciplines.chapters.notions.slug The notion slug
     * @apiSuccess {string} disciplines.chapters.notions.description The notion description
     * @apiSuccess {Activity[]} disciplines.chapters.notion.activities The activities
     * @apiSuccess {int} disciplines.chapters.notions.activities.id The activity id
     * @apiSuccess {string} disciplines.chapters.notions.activities.name The activity name
     * @apiSuccess {string} disciplines.chapters.notions.activities.slug The activity slug
     * @apiSuccess {string} disciplines.chapters.notions.activities.description The activity description
     * @apiError (Error 404) model_not_found Class with given id does not exist
     *
     * @param int $classId The class id
     *
     * @return JsonResponse
     */
    public function program(int $classId)
    {
        $data = Class_::with([
            'disciplines.chapters' => function (HasMany $query) use ($classId) {
                $query->getQuery()
                    ->where('class_id', $classId)
                    ->get(['id', 'name', 'slug']);
            },
            'disciplines.chapters.notions.activities'
        ])->where('id', $classId)->firstOrFail();

        return $this->http200Success($data);
    }



    /**
     * @apiVersion 1.0.0
     * @api {get} /content/modules/{classId} Modules
     * @apiGroup Content
     * @apiName ContentModules
     * @apiDescription Retrieve content data by class id
     * @apiPermission public
     * @apiParam {int} id The class id
     * @apiUse Class
     * @apiSuccess {Discipline[]} disciplines The disciplines
     * @apiSuccess {int} disciplines.id The discipline id
     * @apiSuccess {int} disciplines.name The discipline name
     * @apiSuccess {int} disciplines.slug The discipline slug
     * @apiSuccess {Chapter[]} disciplines.chapters The chapters
     * @apiSuccess {int} disciplines.chapters.id The chapter id
     * @apiSuccess {string} disciplines.chapters.name The chapter name
     * @apiSuccess {string} disciplines.chapters.slug The chapter slug
     * @apiSuccess {Notion[]} disciplines.chapters.notions The notions
     * @apiSuccess {int} disciplines.chapters.notions.id The notion id
     * @apiSuccess {string} disciplines.chapters.notions.name The notion name
     * @apiSuccess {string} disciplines.chapters.notions.slug The notion slug
     * @apiSuccess {string} disciplines.chapters.notions.description The notion description
     * @apiSuccess {Activity[]} disciplines.chapters.notion.activities The activities
     * @apiSuccess {int} disciplines.chapters.notions.activities.id The activity id
     * @apiSuccess {string} disciplines.chapters.notions.activities.name The activity name
     * @apiSuccess {string} disciplines.chapters.notions.activities.slug The activity slug
     * @apiSuccess {string} disciplines.chapters.notions.activities.description The activity description
     * @apiSuccess {Activity[]} disciplines.chapters.notion.activities The activities
     * @apiSuccess {Module[]} disciplines.chapters.notions.activities.modules The modules
     * @apiError (Error 404) model_not_found Class with given id does not exist
     *
     * @param int $classId The class id
     *
     * @return JsonResponse
     */
    public function modules(int $classId)
    {
        $data = Class_::with([
            'disciplines.chapters' => function (HasMany $query) use ($classId) {
                $query->getQuery()
                    ->where('class_id', $classId)
                    ->get(['id', 'name', 'slug']);
            },
            'disciplines.chapters.notions.activities.modules'
        ])->where('id', $classId)->firstOrFail();

        return $this->http200Success($data);
    }


    /**
     * @apiVersion 1.0.0
     * @api {get} /content/search/{query}/{?page}/{?perPage} Search
     * @apiGroup Content
     * @apiName ContentSearch
     * @apiDescription Search for chapters and notions
     * @apiPermission public
     * @apiParam {int} [page=1] The page number
     * @apiParam {int} [perPage=10] The search count per page
     * @apiUse ChapterArray
     * @apiUse NotionArray
     * @apiError (Error 400) invalid_request page and perPage have to be strictly positive
     *
     * @param string $query
     * @param int $page
     * @param int $perPage
     *
     * @return JsonResponse
     */
    public function search(string $query, int $page = 1, $perPage = 10)
    {
        if ($page < 1 || $perPage < 1) {
            return $this->http400InvalidRequest('page and perPage have to be strictly positive');
        }

        $chapters = Chapter::where('name', 'LIKE', '%' . $query . '%')
            ->orWhere('slug', 'LIKE', '%' . $query . '%')
            ->limit($perPage)
            ->offset(($page - 1) * $perPage)
            ->get();
        $notions = Notion::where('name', 'LIKE', '%' . $query . '%')
            ->orWhere('slug', 'LIKE', '%' . $query . '%')
            ->limit($perPage)
            ->offset(($page - 1) * $perPage)
            ->get();

        return $this->http200Success([
            'chapters' => $chapters,
            'notions' => $notions
        ]);

    }
}