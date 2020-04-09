<?php

declare(strict_types=1);

namespace Railgun\Checks;

use Braintree\Gateway;
use Mathrix\Healthz\Checks\Check;
use function count;

class BraintreeCheck extends Check
{
    private Gateway $braintree;

    /**
     * @param Gateway $braintree The Braintree gateway.
     * @param mixed[] $options   The Braintree check otpions.
     */
    public function __construct(Gateway $braintree, array $options = [])
    {
        parent::__construct($options);

        $this->braintree = $braintree;
    }

    /**
     * @return mixed[] The Braintree check details.
     */
    public function execute(): array
    {
        $collection = $this->braintree->transaction()->search([]);
        $data       = [];

        foreach ($collection as $key => $transaction) {
            $data[] = $transaction;
            if (count($data) === 3) {
                break;
            }
        }

        return [
            'latest_3_charges' => $data,
        ];
    }
}
