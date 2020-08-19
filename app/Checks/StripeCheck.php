<?php

declare(strict_types=1);

namespace Railgun\Checks;

use Mathrix\Healthz\Checks\Check;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

class StripeCheck extends Check
{
    protected StripeClient $stripe;

    /**
     * @param StripeClient $stripe  The Stripe client.
     * @param mixed[]      $options The check options.
     */
    public function __construct(StripeClient $stripe, array $options = [])
    {
        parent::__construct($options);

        $this->stripe = $stripe;
    }

    /**
     * @return mixed[] The check details.
     *
     * @throws ApiErrorException
     */
    public function execute(): array
    {
        return [
            'latest_3_charges' => $this->stripe->charges->all(['limit' => 3]),
        ];
    }
}
