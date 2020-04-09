<?php

declare(strict_types=1);

namespace Railgun\Checks;

use Mathrix\Healthz\Checks\Check;
use Stripe\Charge;
use Stripe\Exception\ApiErrorException;

class StripeCheck extends Check
{
    /**
     * @return mixed[] The check details.
     *
     * @throws ApiErrorException
     */
    public function execute(): array
    {
        return [
            'latest_3_charges' => Charge::all(['limit' => 3]),
        ];
    }
}
