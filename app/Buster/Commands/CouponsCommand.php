<?php

declare(strict_types=1);

namespace Railgun\Buster\Commands;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use stdClass;
use function implode;

/**
 * Migrate discounts => coupons
 */
class CouponsCommand extends V7Command
{
    /**
     * @var string $signature The command signature.
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $signature = 'buster:coupons';

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
            ->map(static function (stdClass $coupon) {
                // Ensure that all coupons got non-null created_at and updated_at
                $coupon->created_at ??= Carbon::now();
                $coupon->updated_at ??= Carbon::now();

                return (array)$coupon;
            })
            ->tap(function (Collection $coupons): void {
                $this->line("Found {$coupons->count()} coupons");
                DB::table('coupons')->insert($coupons->toArray());
            });
    }
}
