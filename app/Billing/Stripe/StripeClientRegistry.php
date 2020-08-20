<?php

declare(strict_types=1);

namespace Railgun\Billing\Stripe;

use Railgun\Support\Registry;
use Stripe\StripeClient;

class StripeClientRegistry extends Registry
{
    public const CLIENT_MAIN   = 'main';
    public const CLIENT_LEGACY = 'legacy';

    protected string $defaultPath = 'stripe.defaults.client';
    protected string $configPath  = 'stripe.clients.%s';

    /**
     * @return string[] The client configuration validation rules.
     *
     * @codeCoverageIgnore
     */
    protected function rules(): array
    {
        return [
            'api_key' => 'required|string',
        ];
    }

    /**
     * @param string                      $name    The StripeClient name.
     * @param mixed[]|string $options The StripeClient options ({@see BaseStripeClient::__construct}).
     *
     * @return StripeClient The new StripeClient.
     */
    protected function factory(string $name, $options): StripeClient
    {
        return new StripeClient($options);
    }
}
