<?php

declare(strict_types=1);

namespace Railgun\Billing\Stripe;

use Railgun\Support\Registry;

/**
 * @method StripeWebhook make(?string $name = null)
 */
class StripeWebhookRegistry extends Registry
{
    protected string $defaultPath = 'stripe.defaults.webhooks';
    protected string $configPath  = 'stripe.webhooks.%s';

    /**
     * @return string[]
     */
    protected function rules(): array
    {
        return [
            'secret'    => 'required|string',
            'tolerance' => 'integer|gt:0',
        ];
    }

    /**
     * @param string                     $name    The webhook name.
     * @param mixed[]|string $options The webhook options.
     *
     * @return StripeWebhook The StripeWebhook instance.
     */
    protected function factory(string $name, $options): StripeWebhook
    {
        return new StripeWebhook($name, $options);
    }
}
