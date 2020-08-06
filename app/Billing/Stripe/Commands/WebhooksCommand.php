<?php

declare(strict_types=1);

namespace Railgun\Billing\Stripe\Commands;

use Illuminate\Console\Command;
use Railgun\Billing\Stripe\StripeClientRegistry;
use Railgun\Billing\Stripe\StripeWebhooksController;
use Stripe\StripeClient;
use Stripe\WebhookEndpoint;
use function count;
use function dd;

class WebhooksCommand extends Command
{
    protected $signature = 'stripe:webhooks {--client=}';

    public function handle(StripeClientRegistry $clientRegistry): void
    {
        /** @var StripeClient $client */
        $client = $clientRegistry->make($this->option('client'));

        // Query webhooks
        $results = $client->webhookEndpoints->all(['limit' => 1])->data;

        if (count($results) === 0) {
            // Create the webhook
            return;
        }

        /** @var WebhookEndpoint $webhook */
        $webhook = $results[0];

        $data = [
            'description'    => 'Webhook endpoint for the Railgun ' . app()->environment() . ' environment',
            'enabled_events' => array_keys(StripeWebhooksController::EVENT_MAP),
        ];

        $client->webhookEndpoints->update($webhook->id, $data);
    }
}
