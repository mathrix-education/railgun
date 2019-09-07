<?php

namespace App\Controllers;

use App\Models\Chapter;
use App\Models\User;
use App\Services\Sitemap\Sitemap;
use App\Services\Sitemap\SitemapURL;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Mathrix\Lumen\Zero\Controllers\BaseController;
use Mathrix\Lumen\Zero\Responses\SuccessJsonResponse;
use Thepixeldeveloper\Sitemap\Drivers\XmlWriterDriver;
use Thepixeldeveloper\Sitemap\Extensions\Video;
use Thepixeldeveloper\Sitemap\Url;
use Thepixeldeveloper\Sitemap\Urlset;

/**
 * Class SystemController.
 * Allow control of system services.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 */
class SystemController extends BaseController
{
    private function benchmark(callable $test, int $count = 100, $unit = "ms")
    {
        $start = microtime(true);
        for ($i = 0; $i < $count; $i++) {
            $test($count);
        }
        $duration = microtime(true) - $start; // In seconds
        $mean = $duration / $count;

        switch ($unit) {
            case "ms":
            case "msec":
            case "milliseconds":
                return $mean * 1000;
            default:
                return $mean;
        }
    }


    /**
     * GET /
     * The base application route.
     *
     * @return JsonResponse
     */
    public function base(): JsonResponse
    {
        return new SuccessJsonResponse([
            "message" => "Welcome to the official Mathrix Education REST API!",
            "authors" => ["Mathieu Bour <mathieu@mathrix.fr>"],
            "version" => env("APP_VERSION"),
            "build" => env("APP_BUILD")
        ]);
    }


    /**
     * GET /docs
     * Only available in dev environment.
     *
     * @return Response
     */
    public function docs(): Response
    {
        return new Response(file_get_contents(resource_path("docs/index.html")));
    }


    /**
     * GET /system/health
     * Health check for Docker
     *
     * @param int $count
     *
     * @return JsonResponse
     */
    public function health(int $count = 1): JsonResponse
    {
        $userId = User::random()->id;
        $chapterId = Chapter::random()->id;

        $userQuery = User::query()->where("id", "=", $userId);
        $chapterRecursive = Chapter::with([
            "notions" => function (HasMany $q) {
                $q->where("visible", "=", true);
            },
            "notions.activities" => function (HasMany $q) {
                $q->where("visible", "=", true);
            },
            "notions.activities.modules" => function (HasMany $q) {
                $q->where("visible", "=", true);
            }
        ])
            ->where("id", "=", $chapterId);

        return new SuccessJsonResponse([
            "user_random" => [
                "query" => $userQuery->toSql(),
                "mean($count)" => $this->benchmark(function () use ($userQuery) {
                    $userQuery->firstOrFail();
                }, $count),
            ],
            "chapter_recursive" => [
                "query" => $chapterRecursive->toSql(),
                "mean($count)" => $this->benchmark(function () use ($chapterRecursive) {
                    $chapterRecursive->firstOrFail();
                }, $count)
            ]
        ]);
    }


    /**
     * GET /system/sitemap
     * Dynamically generate the sitemap.
     *
     * @return Response
     */
    public function sitemap(): Response
    {
        $driver = new XmlWriterDriver();
        $sitemap = new Urlset();

        // Add root url
        $home = new Url(loc("/"));
        $sitemap->add($home);

        // Add grade urls
        DB::table("grades")
            ->select(["slug"])
            ->get()
            ->each(function ($data) use ($sitemap) {
                $gradeUrl = new Url(loc($data->slug));
                $sitemap->add($gradeUrl);
            });

        // Add module urls
        DB::table("modules")
            ->selectRaw("
                disciplines.slug AS discipline_slug,
                chapters.slug AS chapter_slug,
                modules.id as module_id,
                modules.seo_title AS module_seo_title,
                modules.seo_slug AS module_seo_slug,
                modules.content AS module_content,
                modules.updated_at AS module_lastmod
            ")
            ->join("activities", "modules.activity_id", "=", "activities.id")
            ->join("notions", "activities.notion_id", "=", "notions.id")
            ->join("chapters", "notions.chapter_id", "=", "chapters.id")
            ->join("disciplines", "chapters.discipline_id", "=", "disciplines.id")
            ->where("seo_index", "=", true)
            ->get()
            ->map(function ($data) use ($sitemap) {
                /*
                 * Search for videos in the module content and if some are found, use the
                 */
                $videos = [];
                if ($data->module_content !== null) {
                    preg_match_all("/\[video.*](.*)\[\/video]/", $data->module_content, $matches);

                    foreach ($matches[1] as $youtubeId) {
                        $video = new Video(
                            "http://img.youtube.com/vi/$youtubeId/sddefault.jpg",
                            $data->module_seo_title,
                            null
                        );
                        $video->setPlayerLoc("https://www.youtube.com/v/$youtubeId");
                        $videos[] = $video;
                    }
                }

                $loc = "{$data->discipline_slug}/{$data->chapter_slug}/{$data->module_seo_slug}-{$data->module_id}";
                $moduleUrl = new Url(loc($loc));
                $moduleUrl->setLastMod(new Carbon($data->module_lastmod));
                $moduleUrl->setPriority(1);

                foreach ($videos as $video) {
                    $moduleUrl->addExtension($video);
                }

                $sitemap->add($moduleUrl);
            });

        $sitemap->accept($driver);
        $response = new Response();
        $response->header("Content-Type", "application/xml");
        $response->setContent($driver->output());

        return $response;
    }
}
