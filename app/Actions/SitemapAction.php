<?php

declare(strict_types=1);

namespace Railgun\Actions;

use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use JsonException;
use Laravel\Lumen\Routing\Controller;
use stdClass;
use Thepixeldeveloper\Sitemap\Drivers\XmlWriterDriver;
use Thepixeldeveloper\Sitemap\Extensions\Video;
use Thepixeldeveloper\Sitemap\Url;
use Thepixeldeveloper\Sitemap\Urlset;
use function array_key_exists;
use function collect;
use function config;
use function implode;
use function is_array;
use function json_decode;
use function trim;
use const JSON_THROW_ON_ERROR;

/**
 * Handles /sitemap route.
 */
class SitemapAction extends Controller
{
    /**
     * @param string|null $path The path, if necessary.
     *
     * @return string The full Angular application path.
     */
    private function loc(?string $path = null): string
    {
        return trim(config('urls.angular'), " \t\n\r \v") . ($path ? '/' . trim($path, " \t\n\r \v") : '');
    }

    public function __invoke(): Response
    {
        $driver  = new XmlWriterDriver();
        $sitemap = new Urlset();

        // Add root url
        $sitemap->add(new Url($this->loc('/')));

        // Add grade urls
        DB::table('grades')
            ->select(['slug'])
            ->where('visible', '=', true)
            ->pluck('slug')
            ->each(fn(string $slug) => $sitemap->add(new Url($this->loc($slug))));

        // Add course urls
        DB::table('courses')
            ->join('notions', 'courses.notion_id', '=', 'notions.id')
            ->join('chapters', 'notions.chapter_id', '=', 'chapters.id')
            ->join('disciplines', 'chapters.discipline_id', '=', 'disciplines.id')
            ->selectRaw(implode(', ', [
                'courses.id AS id',
                'courses.content AS content',
                'courses.slug AS slug',
                'courses.seo_title AS seo_title',
                'courses.updated_at AS updated_at',
                'chapters.slug AS chapter_slug',
                'disciplines.slug AS discipline_slug',
            ]))
            ->where('seo_index', '=', true)
            ->get()
            ->each(function (stdClass $data) use ($sitemap): void {
                $url = new Url($this->loc("$data->discipline_slug/$data->chapter_slug/{$data->slug}-{$data->id}"));
                $url->setLastMod(new Carbon($data->updated_at));
                $url->setPriority('1');

                // @codeCoverageIgnoreStart
                try {
                    $wall = json_decode($data->content, true, 512, JSON_THROW_ON_ERROR);
                } catch (JsonException $exception) {
                    return;
                }
                // @codeCoverageIgnoreEnd

                // Search for video bricks
                collect($wall)
                    ->reject(static function ($brick) {
                        return !is_array($brick)
                            || !array_key_exists('kind', $brick)
                            || $brick['kind'] !== 'video'
                            || !array_key_exists('videoId', $brick);
                    })
                    ->map(static function (array $brick) use ($data) {
                        $youtubeId = $brick['videoId'];

                        $video = new Video(
                            "http://img.youtube.com/vi/$youtubeId/sddefault.jpg",
                            $data->seo_title,
                            null,
                        );
                        $video->setPlayerLoc("https://www.youtube.com/v/$youtubeId");

                        return $video;
                    })
                    ->each(static fn(Video $video) => $url->addExtension($video));

                $sitemap->add($url);
            });

        // Generate the sitemap response
        $sitemap->accept($driver);

        return new Response($driver->output(), 200, [
            'Content-Type' => 'application/xml',
        ]);
    }
}
