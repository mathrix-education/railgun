<?php

declare(strict_types=1);

namespace Railgun\Billing\Stripe;

use DateTimeInterface;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;
use Stripe\Event;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use Stripe\WebhookSignature;
use function config;
use function hash_hmac;
use function is_array;
use function is_string;
use function str_replace;
use function time;

/**
 * Wrapper around {@see Webhook::constructEvent}.
 */
class StripeWebhook
{
    protected string $name;
    protected string $secret;
    protected int    $tolerance;

    /**
     * @param string                      $name   The webhook name.
     * @param array<string, mixed>|string $config The Webhooks configuration.
     */
    public function __construct(string $name, $config)
    {
        $this->name = $name;

        if (is_string($config)) {
            $config = [
                'secret'    => $config,
                'tolerance' => Webhook::DEFAULT_TOLERANCE,
            ];
        }

        if (!is_array($config)) {
            throw new InvalidArgumentException('$config must be a string or an array'); // @codeCoverageIgnore
        }

        Validator::make($config, ['secret' => 'required', 'tolerance' => 'integer'])->validate();

        $this->secret    = $config['secret'];
        $this->tolerance = $config['tolerance'] ?? Webhook::DEFAULT_TOLERANCE;
    }

    /**
     * @param string $payload   The event payload.
     * @param string $signature The event signature.
     *
     * @return Event The parsed and validated event.
     *
     * @throws SignatureVerificationException
     */
    public function parse(string $payload, string $signature): Event
    {
        return Webhook::constructEvent($payload, $signature, $this->secret, $this->tolerance);
    }

    /**
     * Sign a payload from Stripe webhooks.
     *
     * @see WebhookSignature::computeSignature()
     *
     * @param string                 $payload The JSON-encoded payload.
     * @param DateTimeInterface|null $date    The signature timestamp.
     *
     * @return string The signature of the (payload|timestamp).
     */
    public function sign(string $payload, ?DateTimeInterface $date = null): string
    {
        if ($date instanceof DateTimeInterface) {
            $timestamp = $date->getTimestamp(); // @codeCoverageIgnore
        } else {
            $timestamp = time();
        }

        $scheme    = WebhookSignature::EXPECTED_SCHEME;
        $signature = hash_hmac(
            'sha256',
            "{$timestamp}.{$payload}",
            $this->secret,
        );

        return "t={$timestamp},{$scheme}={$signature}";
    }

    /**
     * @return string The resolved route for the webhook configuration name.
     */
    public function route(): string
    {
        return str_replace('{account}', $this->name, config('stripe.webhooks_route.uri'));
    }
}
