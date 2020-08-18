<?php

declare(strict_types=1);

namespace Tests\E2E\Billing\Braintree\Webhooks;

use Braintree\Base;
use Braintree\Digest;
use Braintree\Gateway;
use Braintree\Subscription;
use Braintree\WebhookNotification;
use Braintree\Xml;
use Carbon\Carbon;
use Tests\E2E\GraphQL\GraphQLTestCase;
use function base64_encode;
use function config;
use function str_replace;

class WebhooksTestCase extends GraphQLTestCase
{
    protected Gateway $gateway;

    public function setUp(): void
    {
        parent::setUp();

        $this->gateway = $this->app->make(Gateway::class);
    }

    /**
     * @param string $type The event kind ({@see WebhookNotification}).
     * @param Base   $data The event data object.
     */
    protected function callBraintreeWebhook(string $type, Base $data): void
    {
        if ($data instanceof Subscription) {
            $subject = 'subscription';
        } else {
            $subject = 'unknown';
        }

        $event = WebhookNotification::factory([
            'kind'      => $type,
            'subject'   => [$subject => $data->jsonSerialize()],
            'timestamp' => Carbon::now(),
        ]);

        $payload   = base64_encode(Xml::buildXmlFromArray(['notification' => $event->jsonSerialize()]));
        $signature = $this->sign($payload);

        $this->postBraintree($payload, $signature);
    }

    /**
     * @param string $payload Sign the payload.
     *
     * @return string The payload signature.
     */
    protected function sign(string $payload): string
    {
        return $this->gateway->config->getPublicKey() . '|'
            . Digest::hexDigestSha1($this->gateway->config->getPrivateKey(), $payload);
    }

    /**
     * @param string $payload   The event text payload.
     * @param string $signature The event signature.
     */
    protected function postBraintree(string $payload, string $signature): void
    {
        $this->call(
            'post',
            str_replace('{gateway}', 'primary', config('braintree.webhooks_route.uri')),
            [
                'bt_payload'   => $payload,
                'bt_signature' => $signature,
            ],
        );
    }
}
