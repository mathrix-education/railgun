<?php

namespace App\Controllers;

use App\Exceptions\Http401UnauthorizedException;
use App\Exceptions\Http404ModelNotFoundException;
use App\Models\Activity;
use App\Models\Chapter;
use App\Models\Class_;
use App\Models\Country;
use App\Models\Discipline;
use App\Models\Module;
use App\Models\Notion;
use App\Models\User;
use App\Utils\RouterFront;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Tinify\Source;
use Tinify\Tinify;

/**
 * Class ContentController.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 *
 * @since     1.0.0
 *
 * @copyright Mathrix Education SA
 */
class ContentController extends BaseController
{
    /**
     * Retrieve all routes as csv file
     *
     * @return StreamedResponse
     * @throws Http401UnauthorizedException
     */
    public function routes(Request $request)
    {
        if (!$request->user()->isAdmin()) {
            throw new Http401UnauthorizedException();
        }

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
                "module.id",
                "seo_index"
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
                            $module->id,
                            RouterFront::seoIndex($country, $class, $discipline, $chapter, $notion, $activity, $module) ? 1 : 0
                        ];
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

        $response = new JsonResponse([
            "country" => $country,
            "class" => $class,
            "discipline" => $discipline,
            "chapter" => $chapter,
            "notion" => $notion,
            "activity" => $activity,
            "module" => array_merge(
                $module->toArray(),
                ["seo_index" => RouterFront::seoIndex($country, $class, $discipline, $chapter, $notion, $activity, $module)]
            )
        ]);

        return $response;
    }

    /**
     * @apiVersion     4.0.0
     *
     * @api            {get} /content/search/{query}/{?page}/{?perPage} Search
     * @apiGroup       Content
     * @apiName        ContentSearch
     * @apiDescription Search for chapters and notions
     * @apiPermission  public
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
     * @apiSuccess {string} chapters.class_.slug The class slug
     * @apiSuccess {string} chapters.class_.country_id The class country id
     * @apiSuccess {Country} chapters.class_.country The country
     * @apiSuccess {int} chapters.class_.country.id The country id
     * @apiSuccess {string} chapters.class_.country.name The country name
     * @apiSuccess {string} chapters.class_.country.slug The country slug
     * @apiSuccess {Notion[]} notions Array of notions
     * @apiSuccess {int} notions.id The notion id
     * @apiSuccess {string} notions.name The notion name
     * @apiSuccess {string} notions.slug The notion slug
     * @apiSuccess {int} notions.chapters_id The notion chapter_id
     * @apiSuccess {Chapter} notions.chapters The chapter
     * @apiSuccess {int} notions.chapters.id The chapter id
     * @apiSuccess {string} notions.chapters.name The chapter name
     * @apiSuccess {string} notions.chapters.slug The chapter slug
     * @apiSuccess {Discipline} notions.chapters.discipline The discipline
     * @apiSuccess {int} notions.chapters.discipline.id The discipline id
     * @apiSuccess {string} notions.chapters.discipline.name The discipline name
     * @apiSuccess {string} notions.chapters.discipline.slug The discipline slug
     * @apiSuccess {bool} notions.chapters.discipline.beta If the discipline is in beta
     * @apiSuccess {string} notions.chapters.discipline.image The discipline image
     * @apiSuccess {Class} notions.chapters.class_ The class
     * @apiSuccess {int} notions.chapters.class_.id The class id
     * @apiSuccess {string} notions.chapters.class_.name The class name
     * @apiSuccess {string} notions.chapters.class_.slug The class slug
     * @apiSuccess {string} notions.chapters.class_.country_id The class country id
     * @apiSuccess {Country} notions.chapters.class_.country The country
     * @apiSuccess {int} notions.chapters.class_.country.id The country id
     * @apiSuccess {string} notions.chapters.class_.country.name The country name
     * @apiSuccess {string} notions.chapters.class_.country.slug The country slug
     *
     * @param Request $request The request
     * @param string  $query
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
     * @apiVersion     4.0.0
     *
     * @api            {get} /content/tree Tree
     * @apiGroup       Content
     * @apiName        ContentTree
     * @apiDescription Retrieve all cdn files
     * @apiPermission  public
     * @apiSuccess {string[]} - The cdn files
     *
     * @param Request $request The request
     *
     * @return JsonResponse
     */
    public function tree(Request $request)
    {
        $storage = Storage::disk("cdn");

        $directory = $request->query("directory", "/");

        $directories = $storage->directories($directory);
        foreach ($directories as $key => $v) {
            $directories[$key] = env("APP_CDN") . "/$v/";
        }

        $files = $storage->files($directory);
        foreach ($files as $key => $file) {
            $files[$key] = env("APP_CDN") . "/$file";
        }

        return response()->json(array_merge($directories, $files));
    }

    /**
     * @apiVersion     4.0.0
     *
     * @api            {post} /content/upload Upload
     * @apiGroup       Content
     * @apiName        ContentUpload
     * @apiDescription Upload a file to the cdn
     * @apiPermission  public
     * @apiParam (Data) {file} file The file to upload (must be jpeg,jpg,png,gif,pdf,mp3,mp4)
     * @apiParam (Data) {string} destination The file destination on the cdn
     * @apiParam (Data) {string} name The file destination on the cdn
     * @apiSuccess     url The file url on the cdn
     *
     * @param Request $request The request
     *
     * @throws Http401UnauthorizedException
     * @throws ValidationException
     *
     * @return JsonResponse
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
