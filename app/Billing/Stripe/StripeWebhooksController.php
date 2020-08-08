<?php

declare(strict_types=1);

namespace Railgun\Billing\Stripe;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Railgun\Billing\Exceptions\UnsupportedEventException;
use Railgun\Billing\Stripe\Handlers\CustomerSubscriptionUpdatedHandler;
use Stripe\Event;
use Stripe\Exception\SignatureVerificationException;
use function app;

class StripeWebhooksController
{
    public const    EVENT_MAP          = [
        Event::CUSTOMER_SUBSCRIPTION_CREATED => CustomerSubscriptionUpdatedHandler::class,
        Event::CUSTOMER_SUBSCRIPTION_UPDATED => CustomerSubscriptionUpdatedHandler::class,
        Event::CUSTOMER_SUBSCRIPTION_DELETED => CustomerSubscriptionUpdatedHandler::class,
    ];
    protected const HANDLERS_NAMESPACE = 'Railgun\\Billing\\Stripe\\Handlers';

    /**
     * Entry point for Stripe webhooks.
     *
     * @link https://stripe.com/docs/api/events/types
     *
     * @param Request               $request  The Illuminate HTTP request.
     * @param StripeWebhookRegistry $webhooks The Stripe webhook registry.
     * @param string|null           $account  The Stripe account.
     *
     * @return JsonResponse|array<string|int, mixed>
     *
     * @throws SignatureVerificationException
     */
    public function __invoke(Request $request, StripeWebhookRegistry $webhooks, ?string $account)
    {
        $webhook = $webhooks->make($account);
        $event   = $webhook->parse($request->getContent(), $request->header('Stripe-Signature'));
        $handler = self::EVENT_MAP[$event->type] ?? null;

        if (!$handler) {
            throw new UnsupportedEventException($event->type);
        }

        /** @noinspection PhpPossiblePolymorphicInvocationInspection */
        return app()->call($handler, [
            'event'  => $event,
            'object' => $event->data->object,
        ]);
    }
}
