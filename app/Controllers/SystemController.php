<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\Sitemap\SitemapUtils;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Mathrix\Lumen\Zero\Controllers\BaseController;
use Thepixeldeveloper\Sitemap\Drivers\XmlWriterDriver;
use Thepixeldeveloper\Sitemap\Extensions\Video;
use Thepixeldeveloper\Sitemap\Url;
use Thepixeldeveloper\Sitemap\Urlset;
use function file_get_contents;
use function preg_match_all;
use function resource_path;

/**
 * Serve routes starting with /system.
 * Allow control of system services.
 */
class SystemController extends BaseController
{
    /**
     * GET /
     * Display the Redoc generated documentation.
     */
    public function base(): Response
    {
        $docs = file_get_contents(resource_path('views/docs/index.html'));

        return new Response($docs);
    }

    /**
     * GET /system/sitemap
     * Dynamically generate the sitemap.
     */
    public function sitemap(): Response
    {
        $driver  = new XmlWriterDriver();
        $sitemap = new Urlset();

        // Add root url
        $home = new Url(SitemapUtils::loc('/'));
        $sitemap->add($home);

        // Add grade urls
        DB::table('grades')
            ->select(['slug'])
            ->where('visible', '=', true)
            ->get()
            ->each(static function ($data) use ($sitemap): void {
                $gradeUrl = new Url(SitemapUtils::loc($data->slug));
                $sitemap->add($gradeUrl);
            });

        // Add module urls
        DB::table('modules')
            ->selectRaw('
                disciplines.slug AS discipline_slug,
                chapters.slug AS chapter_slug,
                modules.id as module_id,
                modules.seo_title AS module_seo_title,
                modules.seo_slug AS module_seo_slug,
                modules.content AS module_content,
                modules.updated_at AS module_lastmod
            ')
            ->join('activities', 'modules.activity_id', '=', 'activities.id')
            ->join('notions', 'activities.notion_id', '=', 'notions.id')
            ->join('chapters', 'notions.chapter_id', '=', 'chapters.id')
            ->join('disciplines', 'chapters.discipline_id', '=', 'disciplines.id')
            ->where('seo_index', '=', true)
            ->get()
            ->map(static function ($data) use ($sitemap) {
                /*
                 * Search for videos in the module content and if some are found, use the
                 */
                $videos = [];
                if ($data->module_content !== null) {
                    preg_match_all('/\[video.*](.*)\[\/video]/', $data->module_content, $matches);

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

                /** @var string $loc The internal Angular url */
                $loc = "{$data->discipline_slug}/{$data->chapter_slug}/{$data->module_seo_slug}-{$data->module_id}";

                $moduleUrl = new Url(SitemapUtils::loc($loc));
                $moduleUrl->setLastMod(new Carbon($data->module_lastmod));
                $moduleUrl->setPriority('1');

                foreach ($videos as $video) {
                    $moduleUrl->addExtension($video);
                }

                $sitemap->add($moduleUrl);
            });

        $sitemap->accept($driver);
        $response = new Response();
        $response->header('Content-Type', 'application/xml');
        $response->setContent($driver->output());

        return $response;
    }
}
