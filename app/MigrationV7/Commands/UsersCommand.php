<?php

declare(strict_types=1);

namespace Railgun\MigrationV7\Commands;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use function uuid_create;

class UsersCommand extends V7Command
{
    private const CHUNK_SIZE = 1000;

    /**
     * @var string $signature The command signature.
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $signature = 'v7:users';

    public function handle(): void
    {
        $this->title('Generating users UUID');
        $this->migrateTo('2020_03_14_135641_create_user_uuid_on_users_table');

        DB::table('users')
            ->get(['id'])
            ->pluck('id')
            ->tap(fn(Collection $ids) => $this->getOutput()->progressStart($ids->count()))
            ->chunk(self::CHUNK_SIZE)
            ->each(function (Collection $chunk): void {
                $ids   = $chunk->implode(',');
                $cases = $chunk
                    ->map(static fn($id) => "WHEN $id THEN '" . uuid_create() . "'")
                    ->implode("\n");

                DB::statement(
                    <<<MYSQL
                    UPDATE users
                        SET uuid = CASE id
                        $cases
                        END
                        WHERE id IN($ids)
                    MYSQL,
                );

                $this->getOutput()->progressAdvance($chunk->count());
            })
            ->flatten(1)
            ->tap(fn(Collection $ids) => $this->getOutput()->progressFinish());
    }
}
