<?php

namespace App\Controllers;

use App\Models\Activity;
use App\Models\Chapter;
use App\Models\Class_;
use App\Models\Module;
use App\Models\Notion;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Class ChapterController.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 *
 * @since     1.0.0
 *
 * @copyright Mathrix Education SA
 */
class ChapterController extends BaseController
{
    /**
     * @apiVersion     4.0.0
     *
     * @api            {get} /chapters/{?page}/{?perPage} Index
     * @apiGroup       Chapters
     * @apiName        ChaptersIndex
     * @apiDescription Retrieve chapters
     * @apiPermission  moderator
     * @apiUse         Pagination
     * @apiUse         ChapterArray
     * @apiUse         Http401Unauthorized
     */

    /**
     * @apiVersion     4.0.0
     *
     * @api            {get} /chapters/{id} Get
     * @apiGroup       Chapters
     * @apiName        ChapterGet
     * @apiDescription Get a chapter by id
     * @apiPermission  public
     * @apiParam {int} id The chapter id
     * @apiUse         ChapterModel
     * @apiUse         Http404ModelNotFound
     */

    /**
     * @apiVersion     4.0.0
     *
     * @api            {post} /chapters Add
     * @apiGroup       Chapters
     * @apiName        ChapterAdd
     * @apiDescription Add a chapter
     * @apiPermission  moderator
     * @apiUse         Bearer
     * @apiUse         ChapterRequest
     * @apiUse         ChapterModel
     * @apiUse         Http401Unauthorized
     * @apiUse         Http422ModelValidation
     * @apiUse         Http422ModelSave
     */

    /**
     * @apiVersion     4.0.0
     *
     * @api            {patch} /chapters/{id} Edit
     * @apiGroup       Chapters
     * @apiName        ChapterEdit
     * @apiDescription Edit a chapter
     * @apiPermission  moderator
     * @apiUse         Bearer
     * @apiParam {int} id The chapter id
     * @apiUse         ChapterRequest
     * @apiUse         ChapterModel
     * @apiUse         Http401Unauthorized
     * @apiUse         Http404ModelNotFound
     * @apiUse         Http422ModelSave
     * @apiUse         Http422ModelValidation
     */

    /**
     * @apiVersion     4.0.0
     *
     * @api            {delete} /chapters/{id} Delete
     * @apiGroup       Chapters
     * @apiName        ChapterDelete
     * @apiDescription Delete a chapter
     * @apiUse         Bearer
     * @apiPermission  moderator
     * @apiParam {int} id The chapter id
     * @apiUse         Http401Unauthorized
     * @apiUse         Http404ModelNotFound
     */

    /**
     * @apiVersion     4.0.0
     *
     * @api            {get} /chapters/by-class-discipline/{class_id}/{discipline_id} By Class and Discipline
     * @apiGroup       Chapters
     * @apiName        ChapterClassDiscipline
     * @apiDescription Get chapters by class and discipline
     * @apiUse         Bearer
     * @apiPermission  public
     * @apiParam {int} class_id The class id
     * @apiParam {int} discipline_id The discipline id
     * @apiUse         Http401Unauthorized
     * @apiUse         Http404ModelNotFound
     *
     * @param Request $request       The request
     * @param int     $class_id      The class id
     * @param int     $discipline_id The discipline id
     *
     * @return JsonResponse
     */
    public function by_class_discipline(Request $request, int $class_id, int $discipline_id)
    {
        $fullyVisible = $request->query("fully-visible", false);

        if ($fullyVisible === false) {
            Chapter::withoutAppends();
        }

        $chaptersQuery = Chapter::where([["class_id", $class_id], ["discipline_id", $discipline_id]])
            ->orderBy("order");

        $chapters = $chaptersQuery->get();

        return response()->json($chapters);
    }

    /**
     * @apiVersion     4.0.4
     *
     * @api            {get} /chapters/slug/{chapter_slug}/by-class-slug/{class_slug} Get by slug and class slug
     * @apiGroup       Chapters
     * @apiName        ChapterBySlugAndClassDiscipline
     * @apiDescription Get chapters by class and discipline
     * @apiUse         Bearer
     * @apiPermission  public
     * @apiParam {string} chapter_slug The chapter slug
     * @apiParam {string} class_slug The class slug
     * @apiUse         ChapterModel
     * @apiUse         Http404ModelNotFound
     *
     * @param string $chapterSlug The chapter slug
     * @param string $classSlug   The class slug
     *
     * @return JsonResponse
     */
    public function getBySlugAndClassSlug(string $chapterSlug, string $classSlug): JsonResponse
    {
        $class = Class_::where("slug", "=", $classSlug)
            ->firstOrFail();
        $chapter = Chapter::where([
            ["class_id", "=", $class->id],
            ["slug", "=", $chapterSlug],
        ])
            ->firstOrFail();

        return new JsonResponse($chapter);
    }

    /**
     * @apiVersion     4.0.0
     *
     * @api            {get} /chapter/recursive/{id} Recursive
     * @apiGroup       Chapters
     * @apiName        ChapterRecursive
     * @apiDescription Get a chapter and its linked models recursiveley
     * @apiPermission  public
     * @apiParam {int} id the chapter id
     * @apiUse         ChapterArray
     *
     * @param int $id The chapter id
     *
     * @return JsonResponse
     */
    public function recursive(int $id)
    {
        // Chapter -> Notion
        $chapterNotionLinks = DB::select("SELECT chapter_id,notion_id FROM chapter_notion WHERE chapter_id = ?", [$id]);
        $chapterNotionLinks = new Collection($chapterNotionLinks);
        $notionFlatIds = $chapterNotionLinks->pluck("notion_id")->toArray();

        // Notion -> Activity
        $notionActivityLinks = DB::select(
            "SELECT notion_id,activity_id FROM activity_notion WHERE notion_id IN (" . implode(",", $notionFlatIds) . ")"
        );
        $notionActivityLinks = new Collection($notionActivityLinks);
        $activityFlatIds = $notionActivityLinks->pluck("activity_id")->toArray();

        // Activity -> Module
        $activityModuleLinks = DB::select(
            "SELECT module_id,activity_id FROM activity_module WHERE activity_id IN (" . implode(",", $activityFlatIds) . ")"
        );
        $activityModuleLinks = new Collection($activityModuleLinks);
        $moduleFlatIds = $activityModuleLinks->pluck("module_id")->toArray();

        // Retrieve models
        $chapter = Chapter::findOrFail($id);
        $notions = Notion::whereIn("id", $notionFlatIds)->get();
        $activities = Activity::whereIn("id", $activityFlatIds)
            ->where("visible", "=", true)
            ->get();
        $modules = Module::whereIn("id", $moduleFlatIds)
            ->where("visible", "=", true)
            ->get();

        // Build tree
        foreach ($activities as $key => $activity) {
            $activityModules = $modules->whereIn(
                "id",
                $activityModuleLinks->where("activity_id", "=", $activity->id)
                    ->pluck("module_id")
                    ->toArray()
            );
            $activities[$key]->modules = $activityModules->values();
        }

        foreach ($notions as $key => $notion) {
            $notionActivities = $activities->whereIn(
                "id",
                $notionActivityLinks->where("notion_id", "=", $notion->id)
                    ->pluck("activity_id")
                    ->toArray()
            );
            $notions[$key]->activities = $notionActivities->values();
        }

        $chapter->notions = $notions->values();

        $response = new JsonResponse($chapter);

        return $response;
    }
}
