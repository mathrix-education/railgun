<?php

namespace App\Controllers;

use App\Exceptions\UserAlreadyPremiumException;
use App\Models\User;
use App\Services\Braintree\Exceptions\BraintreeProcessorException;
use App\Services\Braintree\Pipelines\SubscriptionPipeline;
use App\Services\Braintree\Utils\Formatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Mathrix\Lumen\Bases\BaseController;
use Mathrix\Lumen\Exceptions\Http\Http400BadRequestException;
use Mathrix\Lumen\Exceptions\Http\Http401UnauthorizedException;

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
     * @throws BraintreeProcessorException
     * @throws Http400BadRequestException
     * @throws UserAlreadyPremiumException
     * @throws ValidationException
     * @throws \Mathrix\Lumen\Exceptions\ValidationException
     */
    public function create(Request $request): JsonResponse
    {
        $pipeline = new SubscriptionPipeline($request);

        return $pipeline->run();
    }


    /**
     * GET /subscriptions/{subscriptionId}
     * Get a subscription by its id.
     *
     * @param Request $request The Illuminate HTTP request.
     * @param string $subscriptionId The Braintree subscription id.
     *
     * @throws Http401UnauthorizedException
     *
     * @return JsonResponse
     */
    public function find(Request $request, string $subscriptionId)
    {
        /** @var User $user */
        $user = $request->user();

        if (!$user->tokenCan("support")) {
            throw new Http401UnauthorizedException();
        }

        $subscription = \Braintree\Subscription::find($subscriptionId);

        return new JsonResponse(
            Formatter::cast($subscription->jsonSerialize())
        );
    }


    /**
     * POST /subscriptions/{subscriptionId}/cancel
     * Cancel a subscription for a user.
     *
     * @param Request $request The Illuminate HTTP request.
     * @param string $subscriptionId The Braintree subscription id.
     *
     * @return JsonResponse
     * @throws Http400BadRequestException
     * @throws Http401UnauthorizedException
     */
    public function cancel(Request $request, string $subscriptionId)
    {
        /** @var User $user */
        $user = $request->user();

        if (!$user->tokenCan("support")) {
            throw new Http401UnauthorizedException();
        }

        $subscriptionResult = \Braintree\Subscription::cancel($subscriptionId);

        if ($subscriptionResult->success) {
            /** @var \Braintree_Subscription $subscription */
            $subscription = $subscriptionResult->subscription;

            $customerResultSearch = \Braintree\Customer::search([
                \Braintree\CustomerSearch::paymentMethodToken()
                    ->is($subscription->paymentMethodToken),
            ]);

            /** @var User $customer */
            $customer = User::query()
                ->where("braintree_id", "=", $customerResultSearch->firstItem()->id)
                ->firstOrFail();
            $customer->forgetBraintreeCustomerCache();

            return new JsonResponse(
                Formatter::cast($subscription->jsonSerialize())
            );
        }
        throw new Http400BadRequestException(
            $subscriptionResult->jsonSerialize(),
            "Unable to cancel subscription."
        );
    }


    /**
     * Get the user subscriptions.
     *
     * @param Request $request The Illuminate HTTP request.
     * @param int $userId The user id.
     *
     * @throws Http401UnauthorizedException
     *
     * @return JsonResponse
     */
    public function byUser(Request $request, int $userId)
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->id !== $userId && !$user->tokenCan("support")) {
            throw new Http401UnauthorizedException();
        }

        /** @var User $customer */
        $customer = User::query()->findOrFail($userId);
        $customer->requireBraintreeCustomer();

        return new JsonResponse(
            Formatter::cast($customer->getBraintreeSubscriptions())
        );
    }
}
