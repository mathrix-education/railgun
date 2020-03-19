<?php

declare(strict_types=1);

namespace Railgun\MigrationV7\Commands;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use stdClass;

class VotesCommand extends V7Command
{
    protected $signature = 'v7:votes';

    public function handle(): void
    {
        DB::table('votes')
            ->tap(fn() => $this->title('Migrating votes'))
            ->get()
            ->tap(fn() => $this->migrateTo('2020_01_17_042806_drop_votes_table'))
            ->mapWithKeys(static function (stdClass $vote) {
                $key = "{$vote->comment_id}-{$vote->user_id}";
                unset($vote->id);

                return [$key => $vote];
            })
            ->values()
            ->tap(fn() => $this->migrateTo('2020_01_17_042807_recreate_votes_table'))
            ->tap(fn(Collection $votes) => $this->getOutput()->progressStart($votes->count()))
            ->map(fn(stdClass $vote) => (array)$vote)
            ->chunk(1000)
            ->each(function (Collection $votes) {
                DB::table('votes')->insert($votes->toArray());
                $this->getOutput()->progressAdvance($votes->count());
            })
            ->tap(fn(Collection $votes) => $this->getOutput()->progressFinish());
    }
}
