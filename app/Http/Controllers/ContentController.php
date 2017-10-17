<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use App\Models\Class_;
use App\Models\Country;
use App\Models\Module;
use App\Models\Notion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Class ContentController
 *
 * @author Mathrix Back-End Team
 * @package App\Http\Controllers
 * @since 0.8.1
 * @copyright Mathrix
 */
class ContentController extends BaseController
{
    /**
     * @apiVersion 0.8.1
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
     * @apiVersion 0.8.1
     * @api {get} /content/program/{classId} Program
     * @apiGroup Content
     * @apiName ContentProgram
     * @apiDescription Retrieve content data by class id
     * @apiPermission public
     * @apiParam {int} id The class id
     * @apiSuccess {int} id The class id
     * @apiSuccess {string} name The class name
     * @apiSuccess {string} name_short The class short name
     * @apiSuccess {string} slug The class slug
     * @apiSuccess {string} slug_short The class short slug
     * @apiSuccess {string} country_id The class country id
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
     * @apiVersion 0.8.1
     * @api {get} /content/modules/{classId} Modules
     * @apiGroup Content
     * @apiName ContentModules
     * @apiDescription Retrieve content data by class id
     * @apiPermission public
     * @apiParam {int} id The class id
     * @apiSuccess {int} id The class id
     * @apiSuccess {string} name The class name
     * @apiSuccess {string} name_short The class short name
     * @apiSuccess {string} slug The class slug
     * @apiSuccess {string} slug_short The class short slug
     * @apiSuccess {string} country_id The class country id
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
     * @apiVersion 0.8.1
     * @api {get} /content/search/{query}/{?page}/{?perPage} Search
     * @apiGroup Content
     * @apiName ContentSearch
     * @apiDescription Search for chapters and notions
     * @apiPermission public
     * @apiParam {int} [page=1] The page number
     * @apiParam {int} [perPage=10] The search count per page
     * @apiSuccess {Chapter[]} chapters Array of chapters
     * @apiSuccess {int} chapters.id The chapter id
     * @apiSuccess {string} chapters.name The chapter name
     * @apiSuccess {string} chapters.slug The chapter slug
     * @apiSuccess  {int} chapters.class_id The chapter class id
     * @apiSuccess {int} chapters.discipline_id The chapter discipline id
     * @apiSuccess {Discipline} chapters.discipline The discipline
     * @apiSuccess {int} chapters.discipline.id The discipline id
     * @apiSuccess {string} chapters.discipline.name The discipline name
     * @apiSuccess {string} chapters.discipline.slug The discipline slug
     * @apiSuccess {bool} chapters.discipline.beta If the discipline is in beta
     * @apiSuccess {string} chapters.discipline.image The discipline image
     * @apiSuccess {Class} chapters.class_ The class
     * @apiSuccess {int} chapters.class_.id The class id
     * @apiSuccess {string} chapters.class_.name The class name
     * @apiSuccess {string} chapters.class_.name_short The class short name
     * @apiSuccess {string} chapters.class_.slug The class slug
     * @apiSuccess {string} chapters.class_.slug_short The class short slug
     * @apiSuccess {string} chapters.class_.country_id The class country id
     * @apiSuccess {Country} chapters.class_.country The country
     * @apiSuccess {int} chapters.class_.country.id The country id
     * @apiSuccess {string} chapters.class_.country.name The country name
     * @apiSuccess {string} chapters.class_.country.name_short The country short name
     * @apiSuccess {string} chapters.class_.country.slug The country slug
     * @apiSuccess {string} chapters.class_.country.slug_short The country short slug
     * @apiSuccess {Notion[]} notions Array of notions
     * @apiSuccess {int} notions.id The notion id
     * @apiSuccess {string} notions.name The notion name
     * @apiSuccess {string} notions.slug The notion slug
     * @apiSuccess {int} notions.chapter_id The notion chapter_id
     * @apiSuccess {Chapter} notions.chapter The chapter
     * @apiSuccess {int} notions.chapter.id The chapter id
     * @apiSuccess {string} notions.chapter.name The chapter name
     * @apiSuccess {string} notions.chapter.slug The chapter slug
     * @apiSuccess {Discipline} notions.chapter.discipline The discipline
     * @apiSuccess {int} notions.chapter.discipline.id The discipline id
     * @apiSuccess {string} notions.chapter.discipline.name The discipline name
     * @apiSuccess {string} notions.chapter.discipline.slug The discipline slug
     * @apiSuccess {bool} notions.chapter.discipline.beta If the discipline is in beta
     * @apiSuccess {string} notions.chapter.discipline.image The discipline image
     * @apiSuccess {Class} notions.chapter.class_ The class
     * @apiSuccess {int} notions.chapter.class_.id The class id
     * @apiSuccess {string} notions.chapter.class_.name The class name
     * @apiSuccess {string} notions.chapter.class_.name_short The class short name
     * @apiSuccess {string} notions.chapter.class_.slug The class slug
     * @apiSuccess {string} notions.chapter.class_.slug_short The class short slug
     * @apiSuccess {string} notions.chapter.class_.country_id The class country id
     * @apiSuccess {Country} notions.chapter.class_.country The country
     * @apiSuccess {int} notions.chapter.class_.country.id The country id
     * @apiSuccess {string} notions.chapter.class_.country.name The country name
     * @apiSuccess {string} notions.chapter.class_.country.name_short The country short name
     * @apiSuccess {string} notions.chapter.class_.country.slug The country slug
     * @apiSuccess {string} notions.chapter.class_.country.slug_short The country short slug
     * @apiError (Error 400) invalid_request page and perPage have to be strictly positive
     *
     * @param Request $request
     * @param string $query
     * @param int $page
     * @param int $perPage
     *
     * @return JsonResponse
     */
    public function search(Request $request, string $query, int $page = 1, $perPage = 10)
    {
        if ($page < 1 || $perPage < 1) {
            return $this->http400InvalidRequest('page and perPage have to be strictly positive');
        }

        $user = $request->user();

        $chapters = new Collection();
        $notions = new Collection();
        $classIds = [];

        // Videos
        $videos = Module::where('title', 'LIKE', '%' . $query . '%')
            ->limit($perPage)
            ->offset(($page - 1) * $perPage)
            ->get();

        // Custom requests for logged users
        if ($user) {
            /** @var Class_ $userClass */
            $userClass = Class_::find($user->class_id);
            $classIds = $userClass->cursus()->pluck('id')->toArray();

            $chapters = Chapter::whereIn('class_id', $classIds)
                ->with(['discipline', 'class_', 'class_.country'])
                ->where('name', 'LIKE', '%' . $query . '%')
                ->orWhere('slug', 'LIKE', '%' . $query . '%')
                ->limit($perPage)
                ->orderByDesc('class_id')
                ->offset(($page - 1) * $perPage)
                ->get();

            $notions = Notion::where('name', 'LIKE', '%' . $query . '%')
                ->whereHas('chapter.class_', function (Builder $query) use ($classIds) {
                    return $query->whereIn('id', $classIds);
                })
                ->with(['chapter.discipline', 'chapter.class_' => function (BelongsTo $query) use ($classIds) {
                    return $query->orderByDesc('id');
                }, 'chapter.class_.country'])
                ->orWhere('slug', 'LIKE', '%' . $query . '%')
                ->limit($perPage)
                ->offset(($page - 1) * $perPage)
                ->get();
        }

        // Not logged user or not filled collection
        if ($chapters->count() < $perPage) {
            $chaptersToAdd = Chapter::with(['discipline', 'class_', 'class_.country']);

            if (!empty($classIds)) {
                $chaptersToAdd = $chaptersToAdd->whereNotIn('class_id', $classIds);
            }

            $chaptersToAdd = $chaptersToAdd->where('name', 'LIKE', '%' . $query . '%')
                ->orWhere('slug', 'LIKE', '%' . $query . '%')
                ->limit($perPage - $chapters->count())
                ->orderByDesc('class_id')
                ->offset(($page - 1) * $perPage)
                ->get();

            $chapters->merge($chaptersToAdd);
        }

        if ($notions->count() < $perPage) {
            $notionsToAdd = Notion::where('name', 'LIKE', '%' . $query . '%');

            if (!empty($classIds)) {
                $notionsToAdd = $notionsToAdd->whereHas('chapter.class_', function (Builder $query) use ($classIds) {
                    return $query->whereNotIn('id', $classIds);
                });
            }

            $notionsToAdd->with(['chapter.discipline', 'chapter.class_' => function (BelongsTo $query) use ($classIds) {
                return $query->orderByDesc('id');
            }, 'chapter.class_.country'])
                ->orWhere('slug', 'LIKE', '%' . $query . '%')
                ->limit($perPage - $notions->count())
                ->offset(($page - 1) * $perPage)
                ->get();
        }

        return $this->http200Success([
            'videos' => $videos,
            'chapters' => $chapters,
            'notions' => $notions
        ]);
    }
}