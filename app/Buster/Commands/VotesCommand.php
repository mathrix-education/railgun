<?php

declare(strict_types=1);

namespace Railgun\Buster\Commands;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use stdClass;
use function uuid_create;

/**
 * Migrate votes
 */
class VotesCommand extends V7Command
{
    /**
     * @var string $signature The command signature.
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $signature = 'buster:votes';

    public function handle(): void
    {
        $this->title('Migrating votes');

        DB::table('votes')
            ->get()
            ->tap(fn() => $this->migrateTo('2020_01_17_042806_drop_votes_table'))
            ->mapWithKeys(static function (stdClass $vote) {
                $key      = "{$vote->comment_id}-{$vote->user_id}";
                $vote->id = uuid_create();

                return [$key => (array)$vote];
            })
            ->values()
            ->tap(fn() => $this->migrateTo('2020_01_17_042807_recreate_votes_table'))
            ->tap(fn(Collection $votes) => $this->getOutput()->progressStart($votes->count()))
            ->chunk(1000)
            ->each(function (Collection $votes): void {
                DB::table('votes')->insert($votes->toArray());
                $this->getOutput()->progressAdvance($votes->count());
            })
            ->tap(fn(Collection $votes) => $this->getOutput()->progressFinish());
    }
}
