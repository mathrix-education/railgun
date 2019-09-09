<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Chapter;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Mathrix\Lumen\Zero\Controllers\BaseController;
use Mathrix\Lumen\Zero\Responses\SuccessJsonResponse;
use Symfony\Component\Console\Output\BufferedOutput;
use Thepixeldeveloper\Sitemap\Drivers\XmlWriterDriver;
use Thepixeldeveloper\Sitemap\Extensions\Video;
use Thepixeldeveloper\Sitemap\Url;
use Thepixeldeveloper\Sitemap\Urlset;
use function collect;
use function env;
use function explode;
use function loc;
use function microtime;
use function preg_match_all;
use function time;

/**
 * Serve routes starting with /system.
 * Allow control of system services.
 */
class SystemController extends BaseController
{
    /**
     * GET /
     * The base application route.
     */
    public function base() : JsonResponse
    {
        return new SuccessJsonResponse([
            'message' => 'Welcome to the official Mathrix Education REST API!',
            'authors' => ['Mathieu Bour <mathieu@mathrix.fr>'],
            'version' => env('APP_VERSION'),
            'build'   => env('APP_BUILD'),
        ]);
    }

    /**
     * GET /system/health
     * Health check for Docker
     *
     * @param int $count The number of iteration for the benchmark.
     *
     * @return SuccessJsonResponse The benchmarks results.
     */
    public function health(int $count = 1) : SuccessJsonResponse
    {
        $userId    = User::random()->id;
        $chapterId = Chapter::random()->id;

        $userQuery        = User::query()
            ->where('id', '=', $userId);
        $chapterRecursive = Chapter::with([
            'notions'                    => static function (HasMany $q) : void {
                $q->where('visible', '=', true);
            },
            'notions.activities'         => static function (HasMany $q) : void {
                $q->where('visible', '=', true);
            },
            'notions.activities.modules' => static function (HasMany $q) : void {
                $q->where('visible', '=', true);
            },
        ])
            ->where('id', '=', $chapterId);

        return new SuccessJsonResponse([
            'user_random'       => [
                'query'        => $userQuery->toSql(),
                "mean($count)" => $this->benchmark(
                    static function () use ($userQuery) : void {
                        $userQuery->firstOrFail();
                    },
                    $count
                ),
            ],
            'chapter_recursive' => [
                'query'        => $chapterRecursive->toSql(),
                "mean($count)" => $this->benchmark(
                    static function () use ($chapterRecursive) : void {
                        $chapterRecursive->firstOrFail();
                    },
                    $count
                ),
            ],
        ]);
    }

    /**
     * Run a benchmark.
     *
     * @param callable $test  The benchmark.
     * @param int      $count The number of iteration to run.
     * @param string   $unit  The unit of measure.
     *
     * @return float|int The benchmark duration.
     */
    private function benchmark(callable $test, int $count = 100, string $unit = 'ms')
    {
        $start = microtime(true);
        for ($i = 0; $i < $count; $i++) {
            $test($count);
        }
        $duration = microtime(true) - $start; // In seconds
        $mean     = $duration / $count;

        switch ($unit) {
            case 'ms':
            case 'msec':
            case 'milliseconds':
                return $mean * 1000;
            default:
                return $mean;
        }
    }

    /**
     * GET /system/sitemap
     * Dynamically generate the sitemap.
     */
    public function sitemap() : Response
    {
        $driver  = new XmlWriterDriver();
        $sitemap = new Urlset();

        // Add root url
        $home = new Url(loc('/'));
        $sitemap->add($home);

        // Add grade urls
        DB::table('grades')
            ->select(['slug'])
            ->get()
            ->each(static function ($data) use ($sitemap) : void {
                $gradeUrl = new Url(loc($data->slug));
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
            ->map(static function ($data) use ($sitemap) : void {
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

                $loc       =
                    "{$data->discipline_slug}/{$data->chapter_slug}/{$data->module_seo_slug}-{$data->module_id}";
                $moduleUrl = new Url(loc($loc));
                $moduleUrl->setLastMod(new Carbon($data->module_lastmod));
                $moduleUrl->setPriority("1");

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

    /**
     * GET /system/work
     * Run schedule and queue workers.
     */
    public function work() : SuccessJsonResponse
    {
        $start  = time();
        $output = new BufferedOutput();
        Artisan::call('schedule:run', [], $output);
        // Artisan::call("queue:work", [], $output);
        $end = time();

        $lines = collect(explode("\n", $output->fetch()))
            ->reject(static function ($line) {
                return empty($line);
            });

        return new SuccessJsonResponse([
            'duration' => $end - $start,
            'logs'     => $lines,
        ]);
    }
}
