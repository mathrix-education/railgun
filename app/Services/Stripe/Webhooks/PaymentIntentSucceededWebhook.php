<?php

declare(strict_types=1);

namespace App\Services\Stripe\Webhooks;

use Mathrix\Lumen\Zero\Responses\SuccessJsonResponse;
use Stripe\Exception\ApiErrorException;
use Stripe\Invoice;
use Stripe\PaymentIntent;
use Stripe\Plan;
use Stripe\Subscription;

/**
 * Handle payment_intent.succeeded webhook.
 *
 * Change the description when succeeded.
 */
class PaymentIntentSucceededWebhook extends BaseWebhook
{
    public const EVENT = 'payment_intent.succeeded';

    /**
     * Handle the webhook.
     *
     * @param PaymentIntent $paymentIntent The Stripe object handled by the webhook.
     *
     * @return SuccessJsonResponse
     *
     * @throws ApiErrorException
     */
    public function handle($paymentIntent): SuccessJsonResponse
    {
        $response = new SuccessJsonResponse([
            'event_type' => self::EVENT,
            'invoice'    => [
                'id' => $paymentIntent->id,
            ],
        ]);

        if ($paymentIntent->invoice === null) {
            return $response; // @codeCoverageIgnore
        }

        /** @var Invoice $invoice */
        $invoice = Invoice::retrieve([
            'id'     => $paymentIntent->invoice,
            'expand' => ['subscription'],
        ]);

        if ($invoice->subscription === null) {
            return $response; // @codeCoverageIgnore
        }

        /** @var Subscription $subscription */
        $subscription = $invoice->subscription;
        /** @var Plan $plan */
        $plan = $subscription->plan;

        // Make the actual update
        $newDescription = "{$plan->nickname} subscription creation";
        PaymentIntent::update($paymentIntent->id, ['description' => $newDescription]);

        return $response;
    }
}
