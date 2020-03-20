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

class CouponsCommand extends V7Command
{
    protected $signature = 'v7:coupons';

    public function handle(): void
    {
        $this->title('Migrating discounts => coupons');

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
}
