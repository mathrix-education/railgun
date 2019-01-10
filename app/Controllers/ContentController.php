<?php
namespace App\Controllers;

use App\Exceptions\Http401UnauthorizedException;
use App\Models\Activity;
use App\Models\Chapter;
use App\Models\Class_;
use App\Models\Country;
use App\Models\Discipline;
use App\Models\Module;
use App\Models\Notion;
use App\Utils\RouterFront;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Tinify\Source;
use Tinify\Tinify;

/**
 * Class ContentController.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     1.0.0
 * @copyright Mathrix Education SA
 * @package   App\Controllers
 */
class ContentController extends BaseController
{
    /**
     * Retrieve all routes as csv file
     *
     * @param Request $request
     *
     * @return StreamedResponse
     */
    public function routes(Request $request)
    {
        // Init
        $data = [
            [
                "uri",
                "country.name",
                "class.name",
                "discipline.name",
                "chapter.name",
                "notion.name",
                "activity.name",
                "activity.type",
                "activity.seo_title",
                "activity.seo_description",
                "activity.seo_keywords",
                "activity.order",
                "activity.reviewed",
                "module.id",
                "seo_index",
                "premium (N.premium || A.type == \"premium\")"
            ]
        ];

        /** @var Chapter[]|Collection $chapters */
        $chapters = Chapter::all();

        /** @var Chapter $chapter */
        foreach ($chapters as $chapter) {
            $class = $chapter->class_;
            $country = $class->country;
            $discipline = $chapter->discipline;

            /** @var Notion $notion */
            foreach ($chapter->notions as $notion) {
                /** @var Activity $activity */
                $activityCounter = 1;

                foreach ($notion->activities as $activity) {
                    /** @var Module $module */
                    foreach ($activity->modules as $module) {
                        $data[] = [
                            "/{$country->slug}/{$class->slug}/{$discipline->slug}/{$chapter->slug}/{$notion->slug}"
                            . "/{$activity->slug}/{$module->uri_type}/{$module->id}",
                            $country->name,
                            $class->name,
                            $discipline->name,
                            $chapter->name,
                            $notion->name,
                            $activity->name,
                            $activity->type,
                            $activity->seo_title,
                            $activity->seo_description,
                            $activity->seo_keywords,
                            $activityCounter++,
                            $activity->reviewed ? 1 : 0,
                            $module->id,
                            RouterFront::seoIndex($country, $class, $discipline, $chapter, $notion, $activity) ? 1 : 0,
                            ($notion->type == "premium" || $activity->premium) ? 1 : 0
                        ];

                        $activityCounter++;
                    }
                }
            }
        }

        $response = new StreamedResponse(
            function () use ($data) {
                // A resource pointer to the output stream for writing the CSV to
                $handle = fopen("php://output", "w");
                foreach ($data as $row) {
                    // Loop through the data and write each entry as a new row in the csv
                    fputcsv($handle, $row);
                }

                fclose($handle);
            }, 200, [
                "Content-Type" => "text/csv",
                "Content-Disposition" => "attachment; filename=routes.csv"
            ]
        );

        return $response;
    }

    /**
     * Get a flat map of a given content route (country/class/discipline/chapter/notion/activity/module)
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws ValidationException
     */
    public function flat(Request $request)
    {
        $this->validate($request, [
            "country_slug" => "required",
            "class_slug" => "required",
            "discipline_slug" => "required",
            "chapter_slug" => "required",
            "notion_slug" => "required",
            "activity_slug" => "required",
            "module_id" => "required"
        ]);

        /** @var Country $country */
        $country = Country::where("slug", "=", $request->input("country_slug"))
            ->firstOrFail();
        /** @var Class_ $class */
        $class = Class_::where("slug", "=", $request->input("class_slug"))
            ->firstOrFail();
        /** @var Class_ $class */
        $discipline = Discipline::where("slug", "=", $request->input("discipline_slug"))
            ->firstOrFail();
        /** @var Chapter $chapter */
        $chapter = Chapter::where([
            "class_id" => $class->id,
            "discipline_id" => $discipline->id,
            "slug" => $request->input("chapter_slug")
        ])->firstOrFail();
        /** @var Notion $notion */
        $notion = $chapter->notions()
            ->where("notions.slug", "=", $request->input("notion_slug"))
            ->firstOrFail();
        /** @var Activity $activity */
        $activity = $notion->activities()
            ->where("activities.slug", "=", $request->input("activity_slug"))
            ->firstOrFail();
        /** @var Module $module */
        $module = $activity->modules()
            ->where("modules.id", "=", $request->input("module_id"))
            ->firstOrFail();

        $activity->setAttribute("seo_index", RouterFront::seoIndex($country, $class, $discipline, $chapter, $notion, $activity));

        $response = new JsonResponse([
            "country" => $country,
            "class" => $class,
            "discipline" => $discipline,
            "chapter" => $chapter,
            "notion" => $notion,
            "activity" => $activity,
            "module" => $module
        ]);

        return $response;
    }

    /**
     * Global research.
     *
     * @param Request $request The request
     * @param string  $query   The query string.
     * @param int     $page
     * @param int     $perPage
     *
     * @return JsonResponse
     */
    public function searchGlobal(Request $request, string $query, int $page = 1, $perPage = 100)
    {
        $query = trim(mb_strtolower(urldecode($query))); // Trim and decode query
        $query = implode("%%", explode(" ", $query));

        $user = $request->user();

        $chapters = new Collection();
        $notions = new Collection();
        $classIds = [];

        // Videos
        $videos = Activity::where("visible", true)
            ->where("name", "LIKE", "%$query%")
            ->orWhere("slug", "LIKE", "%$query%")
            ->whereHas("modules", function (Builder $q) use ($query) {
                return $q->where("type", "course")
                    ->where("content", "LIKE", "%[video]%");
            })
            ->with([
                "notions", "notions.chapters",
                "notions.chapters.discipline",
                "notions.chapters.class_",
                "notions.chapters.class_.country", "modules" => function (BelongsToMany $q) use ($query) {
                    return $q->where("type", "course")
                        ->where("content", "LIKE", "%[video]%");
                }
            ])
            ->limit($perPage)
            ->offset(($page - 1) * $perPage)
            ->get();

        // Custom requests for logged users
        if ($user && $user->class_id !== null) {
            /** @var Class_ $userClass */
            $userClass = Class_::find($user->class_id);
            $cursus = $userClass->cursus();
            $classIds = $cursus->isNotEmpty() ? $cursus->pluck("id")
                ->toArray() : [];

            $chapters = Chapter::whereIn("class_id", $classIds)
                ->with(["discipline", "class_", "class_.country"])
                ->where("name", "LIKE", "%$query%")
                ->orWhere("slug", "LIKE", "%$query%")
                ->limit($perPage)
                ->orderByDesc("class_id")
                ->offset(($page - 1) * $perPage)
                ->get();

            $notions = Notion::where("name", "LIKE", "%$query%")
                ->whereHas("chapters.class_", function (Builder $query) use ($classIds) {
                    return $query->whereIn("id", $classIds);
                })
                ->with(["chapters.discipline", "chapters.class_" => function (BelongsTo $query) use ($classIds) {
                    return $query->orderByDesc("id");
                }, "chapters.class_.country"])
                ->orWhere("slug", "LIKE", "%$query%")
                ->limit($perPage)
                ->offset(($page - 1) * $perPage)
                ->get();
        }

        // Not logged user or not filled collection
        if ($chapters->count() < $perPage) {
            $chaptersToAdd = Chapter::with(["discipline", "class_", "class_.country"]);

            if (!empty($classIds)) {
                $chaptersToAdd = $chaptersToAdd->whereNotIn("class_id", $classIds);
            }

            $chaptersToAdd = $chaptersToAdd->where("name", "LIKE", "%$query%")
                ->orWhere("slug", "LIKE", "%$query%")
                ->limit($perPage - $chapters->count())
                ->orderByDesc("class_id")
                ->offset(($page - 1) * $perPage)
                ->get();

            $chapters = $chapters->merge($chaptersToAdd);
        }

        if ($notions->count() < $perPage) {
            $notionsToAdd = Notion::where("name", "LIKE", "%$query%");

            if (!empty($classIds)) {
                $notionsToAdd = $notionsToAdd->whereHas("chapters.class_", function (Builder $query) use ($classIds) {
                    return $query->whereNotIn("id", $classIds);
                });
            }

            $notionsToAdd = $notionsToAdd->with(["chapters.discipline", "chapters.class_" => function (BelongsTo $query) use ($classIds) {
                return $query->orderByDesc("id");
            }, "chapters.class_.country"])
                ->orWhere("slug", "LIKE", "%$query%")
                ->limit($perPage - $notions->count())
                ->offset(($page - 1) * $perPage)
                ->get();

            $notions = $notions->merge($notionsToAdd);
        }

        return response()->json([
            "page" => $page,
            "perPage" => $perPage,
            "data" => [
                "videos" => $videos,
                "chapters" => $chapters,
                "notions" => $notions,
            ],
        ]);
    }

    /**
     * Upload a file.
     *
     * @param Request $request The request
     *
     * @throws Http401UnauthorizedException
     * @throws ValidationException
     *
     * @return JsonResponse
     * @deprecated
     */
    public function upload(Request $request)
    {
        if (!$request->user()
            ->isModerator()) {
            throw new Http401UnauthorizedException();
        }

        $this->validate($request, [
            "file" => "required|file|max:15360|mimes:jpeg,jpg,png,gif,pdf,mp3,mp4",
            "destination" => "required",
            "name" => "required",
        ]);

        /** @var UploadedFile $file */
        $file = $request->file("file") ?? $request->input("file");

        $destination = trim($request->input("destination"), "/");
        $name = trim($request->input("name"), "/");

        $file->storeAs(
            $destination,
            $name,
            ["disk" => "cdn"]
        );

        // File is an image, try to optimize it
        if (\in_array($file->getMimeType(), ["image/png", "image/jpeg"], true) && Tinify::getCompressionCount() < 500) {
            $source = Source::fromFile($file->getRealPath());
            $source->toFile($file->getRealPath());
        }

        return response()->json([
            "url" => env("APP_CDN") . "/$destination/$name",
        ]);
    }
}
