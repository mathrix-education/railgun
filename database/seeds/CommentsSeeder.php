<?php

declare(strict_types=1);

use Illuminate\Support\Collection;
use Railgun\Models\Comment;
use Railgun\Models\Vote;

class CommentsSeeder extends AdvancedSeeder
{
    private const COMMENT_PARENTS_COUNT  = 200;
    private const COMMENT_CHILDREN_COUNT = 500;
    private const VOTE_COUNT             = 500;

    public function run(): void
    {
        // Parent comments
        $this->seedFactory(Comment::class, self::COMMENT_PARENTS_COUNT);
        $this->seedFactory(Comment::class, ['name' => 'child', 'count' => self::COMMENT_CHILDREN_COUNT]);
        $this->seedFactory(Vote::class, ['seed' => false, 'count' => self::VOTE_COUNT])
            ->mapWithKeys(static function (array $vote) {
                $key = "{$vote['user_id']}-{$vote['comment_id']}";

                return [$key => $vote];
            })
            ->values()
            ->tap(fn() => $this->writeFactoryMessage(Vote::class))
            ->tap(fn(Collection $votes) => $this->seedCollection($votes, 'votes'));
    }
}
