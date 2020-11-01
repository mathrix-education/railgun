<?php

declare(strict_types=1);

namespace Tests\Utils;

use Railgun\Models\Chapter;
use Railgun\Models\Coupon;
use Railgun\Models\Pack;
use Railgun\Models\Training;
use function in_array;
use function is_string;

class CartInputGenerator
{
    /**
     * Generate a valid CartInput GraphQL input.
     *
     * @param string|string[] $options The products to include in the cart.
     * @param string[]|null   $plans   The subscription plan.
     *
     * @return mixed[] A valid CartInput GraphQL input.
     */
    public static function make($options = [], array $plans = []): array
    {
        if (is_string($options)) {
            $options = [$options];
        }

        $cart = [];

        if (in_array('chapters', $options, true)) {
            $cart['chapters'] = Chapter::query()
                ->inRandomOrder()
                ->whereNotNull('stripe_price_id')
                ->limit(3)
                ->pluck('id')
                ->toArray();
        }

        if (in_array('packs', $options, true)) {
            $cart['packs'] = Pack::query()
                ->inRandomOrder()
                ->where('available', '=', true)
                ->whereNotNull('stripe_price_id')
                ->limit(1)
                ->pluck('id')
                ->toArray();
        }

        if (in_array('trainings', $options, true)) {
            $exclude = [];

            if (in_array('packs', $options, true)) {
                $exclude = Pack::query()
                    ->with('trainings')
                    ->whereIn('id', $cart['packs'])
                    ->get()
                    ->map(static fn(Pack $pack) => $pack->trainings->pluck('id'))
                    ->flatten()
                    ->toArray();
            }

            $cart['trainings'] = Training::query()
                ->inRandomOrder()
                ->whereNotIn('id', $exclude)
                ->where('available', '=', true)
                ->whereNotNull('stripe_price_id')
                ->limit(3)
                ->pluck('id')
                ->toArray();
        }

        if (in_array('coupon', $options, true)) {
            $cart['coupon'] = Coupon::query()
                ->inRandomOrder()
                ->where('counter', '=', '-1')
                ->whereNull('expires_at')
                ->firstOrFail()
                ->getKey();
        }

        $cart['plans'] = $plans;

        return $cart;
    }
}
