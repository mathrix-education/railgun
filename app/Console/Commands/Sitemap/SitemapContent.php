<?php
namespace App\Console\Commands\Sitemap;

use App\Models\Activity;
use App\Models\Chapter;
use App\Models\Class_;
use App\Models\Country;
use App\Models\Discipline;
use App\Models\Module;
use App\Models\Notion;
use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 * Class SitemapContent
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @package App\Console\Commands
 * @since 1.0.1
 * @copyright Mathrix Education SA
 */
class SitemapContent extends Command
{
    use SitemapUtils;
    protected $signature = "sitemap:content";


    /**
     * @throws \Exception
     */
    public function handle()
    {
        $today = Carbon::today()->format("Y-m-d");
        $sitemapArray = [
            [
                "url" => $this->url("/"),
                "lastmod" => $today,
                "priority" => 1
            ],
            [
                "url" => $this->url("/pack-mode-machine"),
                "lastmod" => $today,
                "priority" => 1
            ]
        ];
        $dashboardsSitemap = [];
        $disciplinesSitemap = [];
        $chaptersSitemap = [];
        $notionsSitemap = [];
        $modulesSitemap = [];

        /** @var Country $country */
        foreach (Country::all() as $country) {
            /** @var Class_ $class */
            foreach ($country->classes as $class) {
                $dashboardsSitemap[] = [
                    "url" => $this->url("/{$country->slug}/{$class->slug}"),
                    "lastmod" => $today,
                    "priority" => 0.9
                ];

                /** @var Discipline $discipline */
                foreach ($class->disciplines as $discipline) {
                    $disciplinesSitemap[] = [
                        "url" => $this->url("/{$country->slug}/{$class->slug}/{$discipline->slug}"),
                        "lastmod" => $today,
                        "priority" => 0.8
                    ];

                    $chapters = $discipline->chapters()
                        ->where("chapters.class_id", "=", $class->id)
                        ->get();

                    /** @var Chapter $chapter */
                    foreach ($chapters as $chapter) {
                        $chaptersSitemap[] = [
                            "url" => $this->url("/" . implode("/", [
                                    $country->slug,
                                    $class->slug,
                                    $discipline->slug,
                                    $chapter->slug,
                                ])),
                            "lastmod" => $today,
                            "priority" => 0.7
                        ];

                        /** @var Notion $notion */
                        foreach ($chapter->notions()->where("type", "!=", "premium")->get()
                                 as $notion) {
                            $notionsSitemap[] = [
                                "url" => $this->url("/" . implode("/", [
                                        $country->slug,
                                        $class->slug,
                                        $discipline->slug,
                                        $chapter->slug,
                                        $notion->slug
                                    ])),
                                "lastmod" => $today,
                                "priority" => 0.6
                            ];

                            /** @var Activity $activity */
                            foreach ($notion->activities()
                                         ->withPivot(["order"])
                                         ->orderBy("pivot_order", "asc")
                                         ->limit(2)
                                         ->get()
                                     as $activity) {
                                /** @var Module $module */
                                foreach ($activity->modules()
                                             ->where("premium", 0)
                                             ->get()
                                         as $module) {

                                    $type = $this->getModuleType($module);

                                    if ($type === false) {
                                        continue;
                                    }

                                    $videos = null;
                                    // Add videos into modules
                                    if ($type === "video") {
                                        preg_match_all("/\[video\]([0-9A-Za-z]+)\[\/video\]/", $module->content, $matches);
                                        $videosIds = $matches[1];

                                        foreach ($videosIds as $videoId) {
                                            $videos[] = [
                                                "title" => $activity->name,
                                                "thumbnail_loc" => "http://img.youtube.com/vi/$videoId/sddefault.jpg",
                                                "player_loc" => [
                                                    "player_loc" => "https://www.youtube.com/v/$videoId",
                                                    "allow_embed" => "yes",
                                                    "autoplay" => false
                                                ]
                                            ];
                                        }
                                    }

                                    $modulesSitemap[] = [
                                        "url" => $this->url("/" . implode("/", [
                                                $country->slug,
                                                $class->slug,
                                                $discipline->slug,
                                                $chapter->slug,
                                                $notion->slug,
                                                $activity->slug,
                                                $this->getModuleType($module),
                                                $module->id
                                            ])),
                                        "lastmod" => $today,
                                        "priority" => 0.5,
                                        "videos" => $videos
                                    ];
                                }
                            }
                        }
                    }
                }
            }
        }

        // Finally, register all links
        $this->saveSitemap($dashboardsSitemap, "sitemap.dashboards");
        $this->saveSitemap($disciplinesSitemap, "sitemap.disciplines");
        $this->saveSitemap($chaptersSitemap, "sitemap.chapters");
        $this->saveSitemap($notionsSitemap, "sitemap.notions");
        $this->saveSitemap($modulesSitemap, "sitemap.modules");
        $this->saveSitemap($sitemapArray, "sitemap.main");
    }


    /**
     * Get the url-friendly module type
     *
     * @param Module $module
     *
     * @return string
     * @throws \Exception
     */
    public function getModuleType(Module $module)
    {
        switch ($module->type) {
            case "mcq":
                return "exercice";
            case "mcq-table":
                return "exercice-qcm";
            case "match":
                return "correspondance";
            case "test":
                return "interro";
            case "fill":
                return "texte-trou";
            case "flashcard":
                return "flashcard";
            case "course":
                return preg_match("/\[video\]([0-9A-Za-z]+)\[\/video\]/", $module->content) === 1 ? "video" : "cours";
            default:
                throw new \Exception("Module type {$module->type} is not handled");

        }
    }
}
