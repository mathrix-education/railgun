<?php

namespace App\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Laravelium\Sitemap\Sitemap;
use Mathrix\Lumen\Bases\BaseController;

/**
 * Class SystemController.
 * Allow control of system services.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0
 */
class SystemController extends BaseController
{
    /**
     * GET /
     * The base application route.
     *
     * @return JsonResponse
     */
    public function base(): JsonResponse
    {
        return new JsonResponse([
            "message" => "Welcome to the official Mathrix Education REST API!",
            "authors" => ["Mathieu Bour <mathieu@mathrix.fr>"],
            "version" => env("APP_VERSION"),
            "build" => env("APP_BUILD")
        ]);
    }


    /**
     * GET /system/health
     * Health check for Docker
     *
     * @return JsonResponse
     */
    public function health(): JsonResponse
    {
    }


    public function sitemap()
    {
        $webroot = env("APP_WEBROOT");

        /** @var Sitemap $sitemap */
        $sitemap = app()->make("sitemap");
        $sitemap->add("$webroot");

        // /{gradeSlug}
        DB::table("grades")
            ->select(["slug"])
            ->get()
            ->each(function ($data) use ($webroot, &$sitemap) {
                $gradeSlug = $data->slug;
                $sitemap->add("$webroot/$gradeSlug");
            });

        // /{gradeSlug}
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
            ->map(function ($data) use ($webroot, &$sitemap) {
                // Search for videos
                $videos = [];
                if ($data->module_content !== null) {
                    preg_match_all("/\[video.*\](.*)\[\/video\]/", $data->module_content, $matches);

                    foreach ($matches[1] as $youtubeId) {
                        $videos[] = [
                            "title" => $data->module_seo_title,
                            "thumbnail_loc" => "http://img.youtube.com/vi/$youtubeId/sddefault.jpg",
                            "player_loc" => [
                                "player_loc" => "https://www.youtube.com/v/$youtubeId",
                                "allow_embed" => "yes",
                                "autoplay" => "ap=1"
                            ]
                        ];
                    }
                }

                $sitemap->add(
                    "$webroot/{$data->discipline_slug}/{$data->chapter_slug}/{$data->module_seo_slug}-{$data->module_id}",
                    $data->module_lastmod,
                    1,
                    null,
                    [],
                    $data->module_seo_title,
                    [],
                    $videos
                );
            });

        return $sitemap->render("xml");
    }
}
