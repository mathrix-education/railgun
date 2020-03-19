<?php

declare(strict_types=1);

namespace Railgun\MigrationV7\Commands;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class UsersCommand extends V7Command
{
    private const CHUNK_SIZE = 1000;
    protected $signature     = 'v7:users';

    public function handle(): void
    {
        $this->title('Generating users UUID');
        $this->migrateTo('2020_03_14_135641_create_user_uuid_on_users_table');

        DB::table('users')
            ->get(['id'])
            ->pluck('id')
            ->tap(fn(Collection $ids) => $this->getOutput()->progressStart($ids->count()))
            ->chunk(self::CHUNK_SIZE)
            ->each(function (Collection $chunk) {
                $ids   = $chunk->implode(',');
                $cases = $chunk
                    ->map(fn($id) => "WHEN $id THEN '" . Uuid::uuid4()->toString() . "'")
                    ->implode("\n");

                DB::statement(<<<MYSQL
                UPDATE users
                    SET uuid = CASE id
                    $cases
                    END
                    WHERE id IN($ids)
                MYSQL
                );

                $this->getOutput()->progressAdvance($chunk->count());
            })
            ->flatten(1)
            ->tap(fn(Collection $ids) => $this->getOutput()->progressFinish());
    }
}
