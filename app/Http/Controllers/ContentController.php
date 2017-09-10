<?php
namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Class_;
use App\Models\Country;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\JsonResponse;
use Mathrix\Lumen\Http\Controllers\BaseController;

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
     * @api {get} /content/modules/{activityId} Modules
     * @apiGroup Content
     * @apiName ContentModules
     * @apiDescription Retrieve modules data by activity id
     * @apiPermission public
     * @apiParam {int} id The activity id
     * @apiUse Activity
     * @apiSuccess {Module[]} modules The modules
     * @apiSuccess {int} modules.id The module name
     * @apiSuccess {string} modules.name The module name
     * @apiSuccess {string} modules.slug The module slug
     * @apiSuccess {string=course,fill,match,mcq,mcq-table,test,video} modules.type The module type
     * @apiSuccess {mixed} modules.content
     *  [course]    `string` The course content<br>
     *  [fill]      `null`<br>
     *  [match]     `null`<br>
     *  [mcq]       `null`<br>
     *  [mcq-table] `null`<br>
     *  [test]      `null`<br>
     *  [video]     `string` The youtube video id (e.g. A7xHki1biKE)
     * @apiSuccess {mixed} modules.questions
     *  [course]    `null`<br>
     *  [fill]      `null`<br>
     *  [match]     `string[]` The static propositions<br>
     *  [mcq]       `string[]` The mcq questions<br>
     *  [mcq-table] `string[]` The mcq questions<br>
     *  [test]      `string[]` The test questions<br>
     *  [video]     `null`<br>
     * @apiSuccess {mixed} modules.choices
     *  [course]    `null`<br>
     *  [fill]      `string[]`   The gap choices<br>
     *  [match]     `string[]`   The draggable propositions<br>
     *  [mcq]       `string[][]` The mcq choices (first dimension for the question number, second dimension for the
     *                           question choices)<br>
     *  [mcq-table] `string[]`   The common choices for all questions<br>
     *  [test]      `null`<br>
     *  [video]     `null`<br>
     * @apiSuccess {mixed} modules.solutions
     *  [course]    `null`<br>
     *  [fill]      `null`<br>
     *  [match]     `string[]`   The solutions for each gap (only one per gap)<br>
     *  [mcq]       `string[][]` The solutions for each question (second dimension array represents the right
     *                           answers)<br>
     *  [mcq-table] `string[][]` The solutions for each question (second dimension array represents the right
     *                           answers)<br>
     *  [test]      `string[]`   The solutions for each question<br>
     *  [video]     `null`<br>
     * @apiSuccess {mixed} modules.corrections
     *  [course]    `null`<br>
     *  [fill]      `null`<br>
     *  [match]     `string[][]` The solutions for each question (second dimension array represents a step)<br>
     *  [mcq]       `string[][]` The solutions for each question (second dimension array represents a step)<br>
     *  [mcq-table] `string[][]` The solutions for each question (second dimension array represents a step)<br>
     *  [test]      `string[][]` The solutions for each question (second dimension array represents a step)<br>
     *  [video]     `null`<br>
     * @apiSuccess {mixed} modules.options The modules options
     * @apiSuccess {int} modules.options.duration [test] The test duration (in seconds)
     * @apiSuccess {int} modules.options.starts [video] The video starting time (in seconds)
     * @apiSuccess {int} modules.options.ends [video] The video ends time (in seconds)
     *
     * @param int $activityId
     *
     * @return JsonResponse
     */
    public function modules(int $activityId)
    {
        $data = Activity::with('modules')->where('id', $activityId)->firstOrFail();;

        return $this->http200Success($data);
    }
}