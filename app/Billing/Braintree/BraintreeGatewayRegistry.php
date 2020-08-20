<?php

declare(strict_types=1);

namespace Railgun\Billing\Braintree;

use Braintree\Gateway;
use Railgun\Support\Registry;

class BraintreeGatewayRegistry extends Registry
{
    public const BRAINTREE_GATEWAY_LEGACY = 'legacy';

    protected string $defaultPath = 'braintree.defaults.gateway';
    protected string $configPath  = 'braintree.gateways.%s';

    /**
     * @see Gateway::__construct
     *
     * @return string[] The Braintree Gateway configuration rules.
     */
    protected function rules(): array
    {
        return [
            'environment' => 'required|in:production,sandbox',
            'merchantId'  => 'required',
            'publicKey'   => 'required',
            'privateKey'  => 'required',
        ];
    }

    /**
     * @param string         $name    The Braintree Gateway name.
     * @param string[]|mixed $options The Braintree Gateway options.
     *
     * @return Gateway The Braintree Gateway.
     */
    protected function factory(string $name, $options): Gateway
    {
        return new Gateway($options);
    }
}
