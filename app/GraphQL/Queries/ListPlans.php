<?php

declare(strict_types=1);

namespace Railgun\GraphQL\Queries;

use Illuminate\Support\Collection;
use Railgun\Billing\Models\Plan;

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
        return Plan::all();
    }
}
