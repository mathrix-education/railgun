<?php

declare(strict_types=1);

namespace Railgun\Buster\Commands;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use stdClass;

class OrdersCommand extends V7Command
{
    /**
     * @var string $signature The command signature.
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $signature = 'buster:orders';

    public function handle(): void
    {
        $this->title('Computing exercise and notion orders');

        DB::table('notions')
            ->pluck('id')
            ->tap(fn(Collection $ids) => $this->getOutput()->progressStart($ids->count()))
            ->each(function (int $id): void {
                $courses  = DB::table('courses')
                    ->selectRaw("id, 'courses' AS `table`, `order`")
                    ->where('notion_id', '=', $id);
                $subjects = DB::table('subjects')
                    ->selectRaw("id, 'subjects' AS `table`, `order`")
                    ->where('notion_id', '=', $id);

                $courses->union($subjects)
                    ->orderBy('order')
                    ->get()
                    ->each(static function(stdClass $data, int $order): void {
                        DB::table($data->table)
                            ->where('id', '=', $data->id)
                            ->update(['order' => $order]);
                    });

                $this->getOutput()->progressAdvance();
            })
            ->tap(fn() => $this->getOutput()->progressFinish());
    }
}
