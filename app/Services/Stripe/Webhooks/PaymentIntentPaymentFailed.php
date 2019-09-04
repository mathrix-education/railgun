<?php

namespace App\Services\Stripe\Webhooks;

use Mathrix\Lumen\Zero\Responses\SuccessJsonResponse;
use Stripe\PaymentIntent;
use Stripe\Subscription;

/**
 * Class PaymentIntentPaymentFailed.
 * Trigger by Stripe when a PaymentIntent fails.
 * We mainly use this webhook to cancel the any attached trial subscription.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA
 * @since 6.0.0
 */
class PaymentIntentPaymentFailed
{
    public function handle(PaymentIntent $paymentIntent)
    {
        if (empty($paymentIntent->metadata["subscription"])) {
            return new SuccessJsonResponse();
        }

        /** @var Subscription $subscription The subscription to cancel. */
        $subscription = Subscription::retrieve($paymentIntent->metadata["subscription"]);
        $subscription->cancel();

        return new SuccessJsonResponse([
            "subscription_id" => $subscription->id,
        ]);
    }
}
