<?php

declare(strict_types=1);

namespace App\MigrationV7\Commands;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use stdClass;
use function implode;

class PostsCommand extends V7Command
{
    protected $signature = 'v7:posts';

    public function handle(): void
    {
        $this->title('Merging modules => posts');

        $iterator   = -1;
        $categoryId = 0;

        DB::table('modules')
            ->join('posts', 'modules.id', '=', 'posts.module_id')
            ->selectRaw(implode(',', [
                'posts.id AS id',
                "JSON_EXTRACT(modules.content, '$.silicate') AS content",
                'modules.visible AS visible',
                'modules.order AS `order`',
                'posts.category_id AS category_id',
            ]))
            ->orderBy('posts.category_id')
            ->orderBy('modules.order')
            ->orderBy('modules.id')
            ->get()
            ->tap(function (Collection $posts) {
                $this->line("Found {$posts->count()} posts");
            })
            ->each(static function (stdClass $post) use (&$iterator, &$categoryId) {
                if ($categoryId !== $post->category_id) {
                    $iterator   = -1;
                    $categoryId = $post->category_id;
                }

                $post->order = ++$iterator;

                DB::table('posts')
                    ->where('id', '=', $post->id)
                    ->update((array)$post);
            });
    }
}
