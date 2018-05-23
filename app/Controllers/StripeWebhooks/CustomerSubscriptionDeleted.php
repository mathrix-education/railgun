<?php
namespace App\Controllers\StripeWebhooks;

use App\Exceptions\Http400InvalidRequestException;
use App\Models\Log;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Laravel\Cashier\Subscription;

/**
 * Trait CustomerSubscriptionDeleted
 * Handle the customer.subscription.deleted event
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @package App\Controllers\StripeWebhooks
 * @since 4.0.3
 * @copyright Mathrix Education SA
 */
trait CustomerSubscriptionDeleted
{
    /**
     * Handle customer.subscription.deleted
     *
     * @param array $payload
     *
     * @return JsonResponse
     * @throws \Exception
     */
    public function handleCustomerSubscriptionDeleted(array $payload)
    {
        $customerId = $payload["data"]["object"]["customer"];
        $subscriptionId = $payload["data"]["object"]["id"];

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
            return $this->debugCustomerSubscriptionDeleted($customerId, $subscriptionId);
        }
        // @coverageIgnoreEnd

        if ($subscription->onTrial()) {
            $subscription->skipTrial();
        }

        // @coverageIgnoreStart
        if (!$this->isInTestingEnvironment()) {
            $subscription->cancelNow();
        } // @coverageIgnoreEnd
        else {
            $subscription->markAsCancelled();
        }

        Log::log("user_cancelled_premium", $user, [
            "customer_id" => $user->stripe_id,
            "subscription_id" => $subscription->stripe_id
        ]);

        // TODO: Send email notification

        return new JsonResponse([
            "message" => "Successfully canceled the following subscription of the following customer",
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
     * @coverageIgnore
     */
    public function debugCustomerSubscriptionDeleted(string $customerId, string $subscriptionId): JsonResponse
    {
        return new JsonResponse([
            "message" => "Successfully tried to cancel the following subscription of the following customer",
            "data" => [
                "customer_id" => $customerId,
                "subscription_id" => $subscriptionId
            ]
        ]);
    }
}
