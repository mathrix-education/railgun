<?php

declare(strict_types=1);

namespace App\Services\Stripe\Webhooks;

use Braintree\Result\Error;
use Braintree\Result\Successful;
use Braintree\Subscription as BraintreeSubscription;
use Mathrix\Lumen\Zero\Exceptions\Http\Http400BadRequestException;
use Mathrix\Lumen\Zero\Responses\SuccessJsonResponse;
use Stripe\Event;
use Stripe\SetupIntent;
use function gateway;

class SetupIntentSucceededWebhook extends BaseWebhook
{
    /**
     * Handle setup_intent.succeeded.
     *
     * @param Event $event The Stripe Event
     *
     * @throws Http400BadRequestException
     */
    public function handle(Event $event) : SuccessJsonResponse
    {
        /** @var SetupIntent $setupIntent */
        $setupIntent = $event->data->object;

        if (empty($setupIntent->metadata['braintree_subscription_id'])) {
            return new SuccessJsonResponse([
                'event_type' => $event->type,
            ], ['message' => 'Webhook handled']);
        }

        $braintreeSubscriptionId = $setupIntent->metadata['braintree_subscription_id'];

        /** @var Successful|Error $result */
        $result = gateway()->subscription()->cancel($braintreeSubscriptionId);

        if ($result->success) {
            /** @var BraintreeSubscription $subscription */
            $subscription = $result->subscription;

            return new SuccessJsonResponse([
                'event_type' => $event->type,
                'subscription' => [
                    'id' => $subscription->id,
                    'status' => $subscription->status,
                ],
            ]);
        }

        throw new Http400BadRequestException($result->errors->deepAll());
    }
}
