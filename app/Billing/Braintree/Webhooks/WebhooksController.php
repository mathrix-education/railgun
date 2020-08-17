<?php

declare(strict_types=1);

namespace Railgun\Billing\Braintree\Webhooks;

use Braintree\Exception\InvalidSignature;
use Braintree\WebhookNotification;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Railgun\Billing\Braintree\BraintreeGatewayRegistry;
use Railgun\Billing\Exceptions\InvalidEventSignatureException;
use Railgun\Billing\Exceptions\UnsupportedEventException;
use function app;

class WebhooksController extends Controller
{
    public const EVENT_MAP = [
        WebhookNotification::SUBSCRIPTION_CANCELED               => SubscriptionsWebhook::class . '@upsert',
        WebhookNotification::SUBSCRIPTION_CHARGED_SUCCESSFULLY   => SubscriptionsWebhook::class . '@upsert',
        WebhookNotification::SUBSCRIPTION_CHARGED_UNSUCCESSFULLY => SubscriptionsWebhook::class . '@upsert',
        WebhookNotification::SUBSCRIPTION_EXPIRED                => SubscriptionsWebhook::class . '@upsert',
        WebhookNotification::SUBSCRIPTION_TRIAL_ENDED            => SubscriptionsWebhook::class . '@upsert',
        WebhookNotification::SUBSCRIPTION_WENT_ACTIVE            => SubscriptionsWebhook::class . '@upsert',
        WebhookNotification::SUBSCRIPTION_WENT_PAST_DUE          => SubscriptionsWebhook::class . '@upsert',
    ];

    /**
     * @param Request                  $request  The Illuminate HTTP request.
     * @param BraintreeGatewayRegistry $registry The Braintree Gateway registry.
     * @param string                   $gateway  The Braintree Gateway name to use.
     *
     * @return mixed
     */
    public function __invoke(Request $request, BraintreeGatewayRegistry $registry, string $gateway)
    {
        $braintree = $registry->make($gateway);

        try {
            $event = $braintree->webhookNotification()->parse(
                $request->input('bt_signature'),
                $request->input('bt_payload'),
            );
        } catch (InvalidSignature $exception) {
            throw new InvalidEventSignatureException($exception);
        }

        $handler = self::EVENT_MAP[$event->kind] ?? null;

        if (!$handler) {
            throw new UnsupportedEventException($event->kind);
        }

        $subject = '';

        if (Str::startsWith($event->kind, 'subscription')) {
            $subject = 'subscription';
        }

        return app()->call(self::EVENT_MAP[$event->kind], [
            'event'  => $event,
            $subject => $event->{$subject},
        ]);
    }
}
