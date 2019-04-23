<?php

namespace App\Controllers;

use App\Models\User;
use App\Services\Braintree\BraintreeResponse;
use App\Services\Braintree\Exceptions\BraintreeErrorHandler;
use App\Services\Braintree\Pipelines\SubscriptionPipeline;
use App\Services\Braintree\Utils\Formatter;
use Braintree\Customer;
use Braintree\CustomerSearch;
use Braintree\PaymentMethod;
use Braintree\Subscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Mathrix\Lumen\Bases\BaseController;
use Mathrix\Lumen\Exceptions\Http\Http400BadRequestException;
use Mathrix\Lumen\Exceptions\Http\Http401UnauthorizedException;
use Mathrix\Lumen\Exceptions\Http\Http429TooManyRequestsException;
use Mathrix\Lumen\Exceptions\ValidationException;

/**
 * Class SubscriptionsController.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 4.1.0
 */
class SubscriptionsController extends BaseController
{
    public const PRICE_PER_MONTH = [
        "v4_monthly" => 17.90,
        "v4_annually" => 12.90,
        "v4_biannually" => 9.90,
    ];


    /**
     * POST /subscriptions
     * Create a new subscriptions for a user.
     *
     * @param Request $request The Illuminate HTTP request.
     *
     * @return JsonResponse
     * @throws Http429TooManyRequestsException
     */
    public function create(Request $request): JsonResponse
    {
        $pipeline = new SubscriptionPipeline($request);

        return $pipeline->run();
    }


    /**
     * GET /subscriptions/{subscriptionId}
     * Allowed scopes: admin, handled by registrar.
     * Get a subscription by its id.
     *
     * @param string $subscriptionId The Braintree subscription id.
     *
     * @return JsonResponse
     */
    public function find(string $subscriptionId)
    {
        $subscription = Subscription::find($subscriptionId);

        return new JsonResponse(Formatter::cast($subscription->jsonSerialize()));
    }


    /**
     * PATCH /subscriptions/{subscriptionId}
     * Update a subscription. At the moment, it used to change the payment method of a subscription.
     *
     * @param Request $request The Illuminate HTTP request.
     * @param string $subscriptionId The Braintree subscription id.
     *
     * @return JsonResponse
     * @throws Http400BadRequestException
     * @throws Http401UnauthorizedException
     * @throws ValidationException
     */
    public function update(Request $request, string $subscriptionId): JsonResponse
    {
        // Stage 1: validate the request
        /** @var User $user */
        $user = $request->user();
        $this->validate($request, [
            "payment_method_nonce" => "required",
            "make_default" => "boolean"
        ]);
        $user->requireBraintreeCustomer();


        // Stage 2: Check that the user is the owner of the targeted subscription.
        if (!$user->ownsBraintreeSubscriptionId($subscriptionId) || $user->tokenCan("admin")) {
            throw new Http401UnauthorizedException(["subscription_id" => $subscriptionId]);
        }

        $payload = [];

        // Stage 3: Add the payment method
        if ($request->has("payment_method_nonce")) {
            /** @var PaymentMethod $paymentMethod */
            $paymentMethod = $user->addBraintreePaymentMethod(
                $request->json("payment_method_nonce"),
                $request->json("make_default", true)
            );
            $payload["paymentMethodToken"] = $paymentMethod->token;
        }

        // Stage 4: Update the subscription
        $subscriptionResult = Subscription::update($subscriptionId, $payload);

        if (!$subscriptionResult->success) {
            BraintreeErrorHandler::report($subscriptionResult);
        }

        /** @var Subscription $subscription */
        $subscription = $subscriptionResult->subscription;

        if (
            isset($user->settings["need_paypal_refresh"])
            && $user->settings["need_paypal_refresh"] === true
        ) {
            $user->settings = Arr::except($user->settings, ["need_paypal_refresh"]);
            $user->save();
        }

        return new JsonResponse(Formatter::cast($subscription->jsonSerialize()));
    }


    /**
     * POST /subscriptions/{subscriptionId}/cancel
     * Allowed scopes: admin, handled by registrar.
     * Cancel a subscription for a user.
     *
     * @param Request $request The Illuminate HTTP request.
     * @param string $subscriptionId The Braintree subscription id.
     *
     * @return JsonResponse
     * @throws Http400BadRequestException
     */
    public function cancel(Request $request, string $subscriptionId)
    {
        $cancelNow = $request->json("now", false);

        if ($cancelNow) {
            // Cancel subscription immediately
            /** @var BraintreeResponse $subscriptionResult */
            $subscriptionResult = Subscription::cancel($subscriptionId);
        } else {
            /** @var Subscription $subscription */
            $subscription = Subscription::find($subscriptionId);
            /** @var BraintreeResponse $subscriptionResult */
            $subscriptionResult = Subscription::update($subscriptionId, [
                "numberOfBillingCycles" => $subscription->currentBillingCycle
            ]);
        }


        if (!$subscriptionResult->success) {
            throw new Http400BadRequestException(
                $subscriptionResult->jsonSerialize(),
                "Unable to cancel subscription."
            );
        }

        /** @var Subscription $subscription */
        $subscription = $subscriptionResult->subscription;

        /** @var array|string $query */
        $query = [
            CustomerSearch::paymentMethodToken()->is($subscription->paymentMethodToken),
        ];
        $customerResultSearch = Customer::search($query);

        /** @var User $customer */
        $customer = User::query()
            ->where("braintree_id", "=", $customerResultSearch->firstItem()->id)
            ->firstOrFail();
        $customer->forgetBraintreeCustomerCache();

        return new JsonResponse(Formatter::cast($subscription->jsonSerialize()));
    }


    /**
     * GET /subscriptions/by-user/{userId}
     * Get the user subscriptions.
     *
     * @param Request $request The Illuminate HTTP request.
     * @param int $userId The user id.
     *
     * @return JsonResponse
     * @throws Http400BadRequestException
     * @throws Http401UnauthorizedException
     */
    public function byUser(Request $request, int $userId)
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->id !== $userId && !$user->tokenCan("admin")) {
            throw new Http401UnauthorizedException();
        }

        /** @var User $customer */
        $customer = User::query()->findOrFail($userId);
        $customer->requireBraintreeCustomer();

        return new JsonResponse(Formatter::cast($customer->getBraintreeSubscriptions()));
    }
}
