<?php

declare(strict_types=1);

namespace Railgun\GraphQL\Queries;

use Railgun\Billing\Stripe\Models\ModeMachine;

/**
 * Query listPlans.
 * List the available subscription plans.
 */
class ListPlans
{
    /**
     * @return mixed[] The available subscription plans.
     */
    public function __invoke(): array
    {
        return ModeMachine::PRICES;
    }
}
