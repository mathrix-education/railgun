<?php
namespace App\Controllers\StripeWebhooks;

use App\Exceptions\Http400InvalidRequestException;
use App\Models\Log;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Laravel\Cashier\Subscription;

/**
 * Trait InvoicePaymentSucceeded
 * Handle the invoice.payment_succeeded event
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @package App\Controllers\StripeWebhooks
 * @since 4.0.3
 * @copyright Mathrix Education SA
 */
trait InvoicePaymentSucceeded
{
    /**
     * @param array $payload
     *
     * @return JsonResponse
     * @throws \Exception
     */
    public function handleInvoicePaymentSucceeded(array $payload)
    {
        $customerId = $payload["data"]["object"]["customer"];
        $subscriptionId = $payload["data"]["object"]["subscription"];

        /** @var User $user */
        $user = $this->getUserByStripeId($customerId);
        /** @var Subscription $subscription */
        $subscription = Subscription::where("stripe_id", "=", $subscriptionId)->first();

        // Raise error if we are in production and were unable to find the user or the subscription
        // @coverageIgnoreStart
        if (!$this->isInTestingEnvironment() && ($user === null || $subscription === null)) {
            throw new Http400InvalidRequestException("Unable to find a valid combination of user/subscription", [
                "customer_id" => $customerId,
                "subscription_id" => $subscriptionId
            ]);
        }

        if ($user === null && $this->isInTestingEnvironment()) {
            return $this->debugInvoicePaymentSucceeded($customerId, $subscriptionId);
        }
        // @coverageIgnoreEnd

        $chargeId = $payload["data"]["object"]["charge"];

        $purchase = Purchase::whereStripeId($chargeId)->first();

        if ($purchase !== null) {
            Log::log("user_payment_succeed_for_{$purchase->purchasable_type}", $user, [
                "customer_id" => $user->stripe_id,
                "loggable_id" => $purchase->purchasable_id,
                "loggable_type" => $purchase->purchasable_type
            ]);
        } else {
            Log::log("user_payment_succeed_for_premium", $user, [
                "customer_id" => $user->stripe_id,
                "subscription_id" => $subscription->stripe_id
            ]);
        }


        // TODO: Send email notification

        return new JsonResponse([
            "message" => "Successfully tried to received the invoice of the following customer",
            "data" => [
                "customer_id" => $customerId,
                "subscription_id" => $subscriptionId
            ]
        ]);
    }


    /**
     * @param $customerId
     * @param $subscriptionId
     *
     * @return JsonResponse
     * @codeCoverageIgnore
     */
    public function debugInvoicePaymentSucceeded(string $customerId, string $subscriptionId): JsonResponse
    {
        return new JsonResponse([
            "message" => "Successfully tried to receive the invoice of the following customer",
            "data" => [
                "customer_id" => $customerId,
                "subscription_id" => $subscriptionId
            ]
        ]);
    }
}
