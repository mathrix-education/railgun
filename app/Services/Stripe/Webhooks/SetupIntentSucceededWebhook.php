<?php

declare(strict_types=1);

namespace App\Services\Stripe\Webhooks;

use Braintree\Result\Error;
use Braintree\Result\Successful;
use Braintree\Subscription as BraintreeSubscription;
use Mathrix\Lumen\Zero\Exceptions\Http\Http400BadRequestException;
use Mathrix\Lumen\Zero\Responses\SuccessJsonResponse;
use Stripe\SetupIntent;
use function gateway;

class SetupIntentSucceededWebhook extends BaseWebhook
{
    public const EVENT = 'setup_intent.succeeded';

    /**
     * Handle setup_intent.succeeded.
     *
     * @param SetupIntent $setupIntent The Stripe SetupIntent object.
     *
     * @return SuccessJsonResponse
     *
     * @throws Http400BadRequestException
     */
    public function handle($setupIntent): SuccessJsonResponse
    {
        if (empty($setupIntent->metadata['braintree_subscription_id'])) {
            return new SuccessJsonResponse([
                'event_type' => self::EVENT,
            ], ['message' => 'Webhook handled']);
        }

        $braintreeSubscriptionId = $setupIntent->metadata['braintree_subscription_id'];

        /** @var Successful|Error $result */
        $result = gateway()
            ->subscription()
            ->cancel($braintreeSubscriptionId);

        if ($result->success) {
            /** @var BraintreeSubscription $subscription */
            $subscription = $result->subscription;

            return new SuccessJsonResponse([
                'event_type' => self::EVENT,
                'subscription' => [
                    'braintree_id' => $subscription->id,
                ],
            ]);
        }

        throw new Http400BadRequestException($result->errors->deepAll());
    }
}
