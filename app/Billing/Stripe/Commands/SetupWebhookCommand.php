<?php

declare(strict_types=1);

namespace Railgun\Billing\Stripe\Commands;

use Illuminate\Console\Command;
use Railgun\Billing\Stripe\StripeClientRegistry;
use Railgun\Billing\Stripe\EventsController;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;
use Stripe\WebhookEndpoint;
use function app;
use function array_keys;
use function count;

/**
 * Setup the stripe webhook.
 */
class SetupWebhookCommand extends Command
{
    /**
     * @var string $signature The command signature.
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $signature = 'stripe:setup:webhook {--client=}';

    /**
     * @param StripeClientRegistry $clientRegistry The Stripe client registry.
     *
     * @throws ApiErrorException
     */
    public function handle(StripeClientRegistry $clientRegistry): void
    {
        /** @var StripeClient $client */
        $client = $clientRegistry->make($this->option('client'));

        // Query webhooks
        $results = $client->webhookEndpoints->all(['limit' => 1])->data;

        if (count($results) === 0) {
            // TODO: create the webhook
            return;
        }

        /** @var WebhookEndpoint $webhook */
        $webhook = $results[0];

        $data = [
            'description'    => 'Webhook endpoint for the Railgun ' . app()->environment() . ' environment',
            'enabled_events' => array_keys(EventsController::EVENT_MAP),
        ];

        $client->webhookEndpoints->update($webhook->id, $data);
    }
}
