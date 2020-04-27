<?php

declare(strict_types=1);

namespace Railgun\Buster\Commands;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Railgun\Models\Notion;
use stdClass;

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

        $count1 = DB::table('notions')
            ->where([['name', 'LIKE', '%cours%'], ['id', '<', 2210]])
            ->orWhere('name', 'LIKE', '%fiche%')
            ->update(['type' => Notion::TYPE_SUMMARY, 'order' => 0]);

        $this->line("Updated {$count1} notions");

        $this->line('Patching notion orders');
        $chapterId = -1;
        $iterator  = -1;

        DB::table('notions')
            ->where('type', '=', Notion::TYPE_STANDARD)
            ->orderBy('chapter_id')
            ->orderBy('order')
            ->get(['id', 'chapter_id'])
            ->tap(fn(Collection $notions) => $this->getOutput()->progressStart($notions->count()))
            ->each(function (stdClass $notion) use (&$chapterId, &$iterator): void {
                if ($chapterId !== $notion->chapter_id) {
                    $chapterId = $notion->chapter_id;
                    $iterator  = -1;
                }

                DB::table('notions')
                    ->where('id', '=', $notion->id)
                    ->update(['order' => ++$iterator]);
                $this->getOutput()->progressAdvance();
            })
            ->tap(fn() => $this->getOutput()->progressFinish());
    }
}
