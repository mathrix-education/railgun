<?php

declare(strict_types=1);

namespace App\MigrationV7\Commands;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use stdClass;
use function implode;

class CouponsCommand extends V7Command
{
    protected $signature = 'v7:coupons';

    public function handle(): void
    {
        $this->title('Migrating discounts => coupons');
        $this->coupons();
        $this->couponProduct();
    }

    private function coupons(): void
    {
        DB::table('discounts')
            ->selectRaw(implode(', ', [
                'code AS id',
                'type',
                'value',
                'counter',
                'expire_at AS expires_at',
                'created_at',
                'updated_at',
            ]))
            ->get()
            ->map(fn(stdClass $coupon) => (array)$coupon)
            ->tap(function (Collection $coupons) {
                $this->line("Found {$coupons->count()} coupons");
                DB::table('coupons')->insert($coupons->toArray());
            });
    }

    private function couponProduct(): void
    {
        $this->queryProductType('pack')
            ->union($this->queryProductType('training'))
            ->get()
            ->map(fn($assoc) => (array)$assoc)
            ->tap(function (Collection $assoc) {
                $this->line("Found {$assoc->count()} coupon associations");
                DB::table('coupon_product')->insert($assoc->toArray());
            });
    }

    private function queryProductType(string $type): Builder
    {
        $table = Str::plural($type);

        return DB::table('discount_product')
            ->join('discounts', 'discount_product.discount_id', '=', 'discounts.id')
            ->join($table, static function (JoinClause $join) use ($table) {
                $join->on(DB::raw("CONCAT('product-', discount_product.product_id)"), '=', "{$table}.mapping");
            })
            ->selectRaw(implode(', ', [
                'discounts.code AS coupon_id',
                "'$type' AS product_type",
                "$table.id AS product_id",
            ]));
    }
}
