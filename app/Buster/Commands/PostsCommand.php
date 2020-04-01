<?php

declare(strict_types=1);

namespace Railgun\Buster\Commands;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Railgun\Buster\Utils\SetOrder;
use stdClass;
use function implode;
use function json_decode;
use const JSON_THROW_ON_ERROR;

/**
 * Migrate modules => posts
 */
class PostsCommand extends V7Command
{
    /**
     * @var string $signature The command signature.
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $signature = 'buster:posts';

    public function handle(): void
    {
        $this->title('Migrating modules => posts');

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
            ->tap(fn(Collection $posts) => $this->line("Found {$posts->count()} posts"))
            ->pipe(new SetOrder('category_id'))
            ->map(static function (stdClass $post) {
                // Extract the first video id
                $wall = json_decode($post->content, false, 512, JSON_THROW_ON_ERROR);

                foreach ($wall as $brick) {
                    if ($brick->kind !== 'video') {
                        continue;
                    }

                    $post->video_id = $brick->videoId;
                    break;
                }

                return $post;
            })
            ->each(static function (stdClass $post): void {
                DB::table('posts')
                    ->where('id', '=', $post->id)
                    ->update((array)$post);
            });
    }
}
