<?php

declare(strict_types=1);

namespace Railgun\Billing\Stripe;

use Illuminate\Http\Request;
use Railgun\Exceptions\ConfigurationException;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use function config;

class StripeWebhooksController
{
    /**
     * Entry point for Stripe webhooks.
     *
     * @throws SignatureVerificationException
     */
    public function __invoke(Request $request, ?string $account = null): void
    {
        $account ??= config('stripe.default');
        $this->validateConfig($account);

        $event = Webhook::constructEvent(
            $request->getContent(),
            $request->header('Stripe-Signature'),
            $this->getSecret($account),
            $this->getTolerance($account),
        );
    }

    protected function validateConfig(string $account): void
    {
        if (!config("stripe.accounts.$account.webhooks")) {
            throw new ConfigurationException("Missing stripe.accounts.$account.webhooks. configuration.");
        }

        if (!config("stripe.webhooks.$account.webhooks.secret")) {
            throw new ConfigurationException("Missing stripe.accounts.$account.webhooks.secret configuration.");
        }
    }

    protected function getSecret(string $account): string
    {
        return config("stripe.accounts.$account.webhooks.secret");
    }

    protected function getTolerance(string $account): int
    {
        return config("stripe.accounts.$account.webhooks.secret", Webhook::DEFAULT_TOLERANCE);
    }

    protected function getHandler(string $eventType)
    {

    }
}
