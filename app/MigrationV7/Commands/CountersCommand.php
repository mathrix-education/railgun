<?php

declare(strict_types=1);

namespace Railgun\MigrationV7\Commands;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use function implode;

class CountersCommand extends V7Command
{
    private const CHUNK_SIZE = 1000;

    /**
     * @var string $signature The command signature.
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $signature = 'v7:counters';

    public function handle(): void
    {
        $this->title('Refreshing comments counters');
        DB::table('votes')
            ->selectRaw(implode(',', [
                'comment_id as id',
                'sum(value) as score',
            ]))
            ->groupBy('comment_id')
            ->get()
            ->tap(fn(Collection $data) => $this->getOutput()->progressStart($data->count()))
            ->chunk(self::CHUNK_SIZE)
            ->each(function (Collection $chunk): void {
                $ids   = $chunk->map(static fn($c) => $c->id)->implode(',');
                $cases = $chunk
                    ->map(static fn($c) => "WHEN $c->id THEN $c->score")
                    ->implode("\n");

                DB::statement(
                    <<<MYSQL
                    UPDATE comments
                        SET score = CASE id
                        $cases
                        END
                        WHERE id IN($ids)
                    MYSQL,
                );

                $this->getOutput()->progressAdvance($chunk->count());
            })
            ->tap(fn() => $this->getOutput()->progressFinish());
        // select comment_id, sum(value) as score from votes group by comment_id
    }
}
