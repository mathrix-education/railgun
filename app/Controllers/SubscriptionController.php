<?php

namespace App\Controllers;

use App\Exceptions\Http400InvalidPlanException;
use App\Exceptions\Http400InvalidRequestException;
use App\Exceptions\Http401UnauthorizedException;
use App\Exceptions\Http422ModelSaveException;
use App\Exceptions\Http422ModelValidationException;
use App\Models\User;
use App\Services\Braintree\Exceptions\BraintreeProcessorException;
use App\Services\Braintree\FamilyPackUtils;
use App\Services\Braintree\Models\Plan;
use App\Services\Braintree\Pipelines\SubscriptionPipeline;
use App\Services\Braintree\Utils\Formatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Routing\Controller;

/**
 * Class SubscriptionController.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     4.1.0
 * @copyright Mathrix Education SA
 * @package   App\Controllers
 */
class SubscriptionController extends Controller
{
    public const PRICE_PER_MONTH = [
        "v4_monthly" => 17.90,
        "v4_annually" => 12.90,
        "v4_biannually" => 9.90,
    ];


    /**
     * Estimate the price of the trial before the regular subscription billing cycles.
     *
     * @param string $planId
     * @param int $additionalChildren
     *
     * @throws Http400InvalidPlanException
     *
     * @return JsonResponse
     */
    public function estimate(string $planId, $additionalChildren = 0)
    {
        if (!\in_array($planId, array_keys(self::PRICE_PER_MONTH), true)) {
            throw new Http400InvalidPlanException($planId);
        } else if ($planId === "v4_monthly") {
            throw new Http400InvalidPlanException($planId);
        }

        $plan = Plan::findOrFail($planId);
        $data = [
            "planId" => $plan->id,
            "trialPrice" => $plan->trialPrice,
            "trialEndDate" => $plan->trialEndDate->__toString() // Hack?
        ];

        if ($additionalChildren > 0) {
            $data["addOns"] = FamilyPackUtils::getAddOnsAsLineItemsForTrial($plan->id, $additionalChildren);
        }

        $response = new JsonResponse($data);

        return $response;
    }


    /**
     * Get a transaction by its id.
     *
     * @param Request $request The request
     * @param string $subscriptionId The Braintree subscription id
     *
     * @throws Http401UnauthorizedException
     *
     * @return JsonResponse
     */
    public function find(Request $request, string $subscriptionId)
    {
        /** @var User $user */
        $user = $request->user();

        if (!$user->isSupport()) {
            throw new Http401UnauthorizedException();
        }

        $subscription = \Braintree_Subscription::find($subscriptionId);

        return new JsonResponse(
            Formatter::cast($subscription->jsonSerialize())
        );
    }


    /**
     * Create a new subscriptions for a user.
     *
     * @param Request $request The request
     *
     * @return JsonResponse
     * @throws Http400InvalidRequestException
     * @throws Http422ModelSaveException
     * @throws Http422ModelValidationException
     * @throws ValidationException
     * @throws BraintreeProcessorException
     */
    public function create(Request $request): JsonResponse
    {
        $pipeline = new SubscriptionPipeline($request);

        return $pipeline->run();
    }


    /**
     * Cancel a subscription for a user.
     *
     * @param Request $request The request
     * @param string $subscriptionId The Braintree subscription id
     *
     * @throws Http400InvalidRequestException
     * @throws Http401UnauthorizedException
     *
     * @return JsonResponse
     */
    public function cancel(Request $request, string $subscriptionId)
    {
        /** @var User $user */
        $user = $request->user();

        if (!$user->isSupport()) {
            throw new Http401UnauthorizedException();
        }

        $subscriptionResult = \Braintree_Subscription::cancel($subscriptionId);

        if ($subscriptionResult->success) {
            /** @var \Braintree_Subscription $subscription */
            $subscription = $subscriptionResult->subscription;

            $customerResultSearch = \Braintree\Customer::search([
                \Braintree_CustomerSearch::paymentMethodToken()
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
        throw new Http400InvalidRequestException("Unable to cancel subscription.", 400, null, [
            "errors" => $subscriptionResult->errors,
        ]);
    }


    /**
     * Get the user subscriptions.
     *
     * @param Request $request The request
     * @param int $userId The user id
     *
     * @throws Http400InvalidRequestException
     * @throws Http401UnauthorizedException
     *
     * @return JsonResponse
     */
    public function byUser(Request $request, int $userId)
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->id !== $userId && !$user->isSupport()) {
            throw new Http401UnauthorizedException();
        }

        /** @var User $customer */
        $customer = User::findOrFail($userId);
        $customer->requireBraintreeCustomer();

        return new JsonResponse(
            Formatter::cast($customer->getBraintreeSubscriptions())
        );
    }
}
