<?php

declare(strict_types=1);

namespace Railgun\Buster\Commands;

use Illuminate\Support\Facades\DB;
use Railgun\Models\Notion;

class NotionCommand extends V7Command
{
    /**
     * @var string $signature The command signature.
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $signature = 'buster:notions';

    /**
     * As requested in the following issue, patch the notions type.
     *
     * @link https://github.com/mathrix-education/education-api/issues/279
     */
    public function handle(): void
    {
        $this->title('Patching summary notions');

        $this->migrateTo('2020_03_29_160931_patch_notions_table');

        $count = DB::table('notions')
            ->where([['name', 'LIKE', '%cours%'], ['id', '<', 2210]])
            ->orWhere('name', 'LIKE', '%fiche%')
            ->update(['type' => Notion::TYPE_SUMMARY]);

        $this->line("Updated {$count} notions");
    }
}
