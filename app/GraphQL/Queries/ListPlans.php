<?php

declare(strict_types=1);

namespace Railgun\GraphQL\Queries;

use Illuminate\Support\Collection;
use Railgun\Billing\Models\Plan;
use Railgun\Billing\Stripe\Models\ModeMachine;
use function collect;
use function round;

/**
 * Query listPlans.
 * List the available subscription plans.
 */
class ListPlans
{
    /**
     * @return Collection|Plan[] The available subscription plans.
     */
    public function __invoke(): Collection
    {
        return collect(ModeMachine::PRICES)->map(static function (array $details, string $planId) {
            return [
                'id'               => $planId,
                'period'           => $details['period'],
                'price_per_period' => $details['unit_amount'],
                'price_per_month'  => round($details['unit_amount'] / $details['months_per_period'], 0),
            ];
        });
    }
}
