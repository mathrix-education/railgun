<?php

declare(strict_types=1);

namespace Railgun\MigrationV7\Commands;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use stdClass;
use function implode;
use function sprintf;

class AcquisitionsCommand extends V7Command
{
    protected $signature = 'v7:acquisitions';

    public function handle(): void
    {
        $this->title('Migrating purchases => acquisitions');
        $this
            ->query('pack')
            ->unionAll($this->query('training'))
            ->get()
            ->unique(fn(stdClass $a) => sprintf('%s-%s-%s', $a->user_id, $a->product_type, $a->product_id))
            ->map(fn(stdClass $acquisition) => (array)$acquisition)
            ->tap(function (Collection $acquisitions) {
                $this->line("Found {$acquisitions->count()} acquisitions");
                DB::table('acquisitions')->insert($acquisitions->toArray());
            });
    }

    private function query($type): Builder
    {
        $table = Str::plural($type);

        return DB::table('purchases')
            ->join('products', static function (JoinClause $join) use ($type) {
                $join->on('purchases.product_id', '=', 'products.id')
                    ->where('products.type', '=', $type);
            })
            ->join($table, static function (JoinClause $join) use ($table, $type) {
                $join->on(DB::raw("CONCAT('product-', purchases.product_id)"), '=', "{$table}.mapping")
                    ->where('products.type', '=', $type);
            })
            ->selectRaw(implode(', ', [
                'purchases.user_id AS user_id',
                'products.type AS product_type',
                "{$table}.id AS product_id",
                'purchases.transaction_id AS transaction_id',
                'purchases.created_at AS created_at',
                'purchases.updated_at AS updated_at',
            ]));
    }
}
