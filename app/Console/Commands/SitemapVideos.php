<?php
namespace App\Console\Commands;

use App\Models\Post;
use Illuminate\Console\Command;

/**
 * Class SitemapVideos
 *
 * @author Mathrix Back-End Team
 * @package App\Console\Commands
 * @since 1.0.1
 * @copyright Mathrix Education SA
 */
class SitemapVideos extends Command
{
    use SitemapUtils;
    protected $signature = "sitemap:videos";


    public function handle()
    {
        $posts = Post::where("content", "LIKE", "%[video]%[/video]")->get();
        $postsSitemap = [];

        foreach ($posts as $post) {
            $postSitemap = [
                "url" => $this->url("/blog/{$post->slug}"),
                "lastmod" => $post->updated_at->format("Y-m-d"),
                "videos" => []
            ];
            preg_match_all("/\[video\]([0-9A-Za-z]+)\[\/video\]/", $post->content, $matches);
            $videosIds = $matches[1];

            foreach ($videosIds as $videoId) {
                $postSitemap["videos"][] = [
                    "thumbnail_loc" => "http://img.youtube.com/vi/$videoId/sddefault.jpg",
                    "player_loc" => [
                        "player_loc" => "https://www.youtube.com/v/$videoId",
                        "allow_embed" => "yes",
                        "autoplay" => null
                    ]
                ];
            }

            $postsSitemap[] = $postSitemap;
        }

        $this->saveSitemap($postsSitemap, "sitemap.videos");
    }

}
