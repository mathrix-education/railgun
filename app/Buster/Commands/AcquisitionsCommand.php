<?php

declare(strict_types=1);

namespace Railgun\Buster\Commands;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use stdClass;
use function array_merge;
use function implode;
use function sprintf;
use function uuid_create;

/**
 * Migrate purchases => acquisitions
 */
class AcquisitionsCommand extends V7Command
{
    /**
     * @var string $signature The command signature.
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $signature = 'buster:acquisitions';

    public function handle(): void
    {
        $this->title('Migrating purchases => acquisitions');
        $this->query('pack')
            ->unionAll($this->query('training'))
            ->get()
            ->unique(static fn(stdClass $a) => sprintf('%s-%s-%s', $a->user_id, $a->product_type, $a->product_id))
            ->map(static function(stdClass $acquisition) {
                return array_merge((array)$acquisition, ['id' => uuid_create()]);
            })
            ->tap(function (Collection $acquisitions): void {
                $this->line("Found {$acquisitions->count()} acquisitions");
                DB::table('acquisitions')->insert($acquisitions->toArray());
            });

        // Add the missing training acquisitions
        $mappings = DB::table('pack_training')->get()->toArray();
        $packs    = [];

        foreach ($mappings as $mapping) {
            if (!isset($packs[$mapping->pack_id])) {
                $packs[$mapping->pack_id] = [];
            }

            $packs[$mapping->pack_id][] = $mapping->training_id;
        }

        $packAcquisitions = DB::table('acquisitions')
            ->where('product_type', '=', 'pack')
            ->get();

        $trainingAcquisitions = [];

        foreach ($packAcquisitions as $packAcquisition) {
            foreach ($mappings[$packAcquisition->product_id] as $trainingId) {
                $trainingAcquisitions[] = array_merge((array)$packAcquisition, [
                    'id'           => uuid_create(),
                    'product_type' => 'training',
                    'product_id'   => $trainingId,
                ]);
            }
        }

        DB::table('acquisitions')->insertOrIgnore($trainingAcquisitions);
    }

    private function query(string $type): Builder
    {
        $table = Str::plural($type);

        return DB::table('purchases')
            ->join('products', static function (JoinClause $join) use ($type): void {
                $join->on('purchases.product_id', '=', 'products.id')
                    ->where('products.type', '=', $type);
            })
            ->join($table, static function (JoinClause $join) use ($table, $type): void {
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
