<?php
namespace App\Controllers;

use App\Exceptions\Http401UnauthorizedException;
use App\Models\Activity;
use App\Models\Chapter;
use App\Models\Class_;
use App\Models\Notion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

/**
 * Class ContentController
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @package App\Controllers
 * @since 1.0.0
 * @copyright Mathrix Education SA
 */
class ContentController extends BaseController
{
    /**
     * @apiVersion 4.0.0
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
     * @apiSuccess {string} notions.chapters.class_.name_short The class short name
     * @apiSuccess {string} notions.chapters.class_.slug The class slug
     * @apiSuccess {string} notions.chapters.class_.slug_short The class short slug
     * @apiSuccess {string} notions.chapters.class_.country_id The class country id
     * @apiSuccess {Country} notions.chapters.class_.country The country
     * @apiSuccess {int} notions.chapters.class_.country.id The country id
     * @apiSuccess {string} notions.chapters.class_.country.name The country name
     * @apiSuccess {string} notions.chapters.class_.country.name_short The country short name
     * @apiSuccess {string} notions.chapters.class_.country.slug The country slug
     * @apiSuccess {string} notions.chapters.class_.country.slug_short The country short slug
     *
     * @param Request $request
     * @param string $query
     * @param int $page
     * @param int $perPage
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
            ->with(["notions", "notions.chapters", "notions.chapters.discipline", "notions.chapters.class_",
                "notions.chapters.class_.country", "modules" => function (BelongsToMany $q) use ($query) {
                    return $q->where("type", "course")
                        ->where("content", "LIKE", "%[video]%");
                }])
            ->limit($perPage)
            ->offset(($page - 1) * $perPage)
            ->get();

        // Custom requests for logged users
        if ($user && $user->class_id !== null) {
            /** @var Class_ $userClass */
            $userClass = Class_::find($user->class_id);
            $cursus = $userClass->cursus();
            $classIds = $cursus->isNotEmpty() ? $cursus->pluck("id")->toArray() : [];

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
                $notionsToAdd = $notionsToAdd->whereHas("chapters.class_", function (Builder $query)
                use ($classIds) {
                    return $query->whereNotIn("id", $classIds);
                });
            }

            $notionsToAdd = $notionsToAdd->with(["chapters.discipline", "chapters.class_" => function (BelongsTo $query)
            use ($classIds) {
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
                "notions" => $notions
            ]
        ]);
    }


    /**
     * @apiVersion 4.0.0
     * @api {get} /content/tree Tree
     * @apiGroup Content
     * @apiName ContentTree
     * @apiDescription Retrieve all cdn files
     * @apiPermission public
     * @apiSuccess {string[]} - The cdn files
     * @return JsonResponse
     */
    public function tree()
    {
        $tree = Storage::disk("cdn")->allFiles();

        foreach ($tree as $key => $file) {
            $tree[$key] = env("APP_CDN") . "/$file";
        }

        return response()->json($tree);
    }


    /**
     * @apiVersion 4.0.0
     * @api {post} /content/upload Upload
     * @apiGroup Content
     * @apiName ContentUpload
     * @apiDescription Upload a file to the cdn
     * @apiPermission public
     * @apiParam (Data) {file} file The file to upload (must be jpeg,jpg,png,gif,pdf,mp3,mp4)
     * @apiParam (Data) {string} destination The file destination on the cdn
     * @apiParam (Data) {string} name The file destination on the cdn
     * @apiSuccess url The file url on the cdn
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws Http401UnauthorizedException
     * @throws ValidationException
     */
    public function upload(Request $request)
    {
        if (!$request->user()->isModerator()) {
            throw new Http401UnauthorizedException();
        }

        $this->validate($request, [
            "file" => "required|file|max:15360|mimes:jpeg,jpg,png,gif,pdf,mp3,mp4",
            "destination" => "required",
            "name" => "required"
        ]);
        /** @var UploadedFile $file */
        $file = $request->input("file");
        $file->storeAs(
            $request->input("destination"),
            $request->input("name"),
            ["disk" => "cdn"]
        );

        return response()->json([
            "url" => env("APP_CDN") . "/{$request->input("destination")}/{$request->input("name")}"
        ]);
    }
}
