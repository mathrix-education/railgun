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
use DateTimeInterface;
use JsonSerializable;
use Railgun\Billing\Braintree\BraintreeGatewayRegistry;
use Tests\E2E\GraphQL\GraphQLTestCase;
use function base64_encode;
use function config;
use function is_array;
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
     * Recursively serialize a Braintree object.
     *
     * @param mixed $object The input object.
     *
     * @return mixed|mixed[]
     */
    protected function serialize($object)
    {
        $data = [];

        if ($object instanceof DateTimeInterface) {
            return $object;
        }

        if (is_array($object)) {
            foreach ($object as $key => $value) {
                $data[$key] = $this->serialize($value);
            }

            return $data;
        }

        if ($object instanceof JsonSerializable) {
            foreach ($object->jsonSerialize() as $key => $value) {
                $data[$key] = $this->serialize($value);
            }

            return $data;
        }

        return $object;
    }

    /**
     * @param string $type   The event kind ({@see WebhookNotification}).
     * @param Base   $object The event data object.
     */
    protected function callBraintreeWebhook(string $type, Base $object): void
    {
        if ($object instanceof Subscription) {
            $subject = 'subscription';
        } else {
            $subject = 'unknown';
        }

        $event = WebhookNotification::factory([
            'kind'      => $type,
            'subject'   => [$subject => $this->serialize($object)],
            'timestamp' => Carbon::now(),
        ]);

        $payload   = base64_encode(Xml::buildXmlFromArray(['notification' => $this->serialize($event)]));
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
            str_replace(
                '{gateway}',
                BraintreeGatewayRegistry::BRAINTREE_GATEWAY_LEGACY,
                config('braintree.webhooks_route.uri'),
            ),
            [
                'bt_payload'   => $payload,
                'bt_signature' => $signature,
            ],
        );
    }
}
