<?php
namespace App\Controllers\Braintree;

use App\Exceptions\Http400InvalidPlanException;
use App\Exceptions\Http400InvalidRequestException;
use App\Exceptions\Http401UnauthorizedException;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Zend\Diactoros\Response\JsonResponse;

/**
 * Class SubscriptionController
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @package App\Controllers\Braintree
 * @since 4.1.0
 * @copyright Mathrix Education SA
 */
class SubscriptionController extends BraintreeController
{
    private const PRICE_PER_MONTH = [
        "v4_monthly" => 17.90,
        "v4_annually" => 12.90,
        "v4_biannually" => 9.90
    ];


    /**
     * Generate an unique transaction id based on an uuid and the prefix sub_
     *
     * @return string
     * @throws Http400InvalidRequestException
     */
    private function generateTransactionId()
    {
        return $this->generateId("sub_");
    }


    /**
     * Get the start date for a given plan
     *
     * @param string $plan
     *
     * @return Carbon
     */
    private function getStartDate(string $plan)
    {
        $nextThirtyFirstJuly = new Carbon("31st July");

        if ($nextThirtyFirstJuly->isPast()) {
            $nextThirtyFirstJuly->addYear();
        } else if ((new Carbon("1st May"))->isPast()) {
            $nextThirtyFirstJuly->addYear();
        }

        switch ($plan) {
            case "v4_annually":
                break;
            case "v4_biannually":
                $nextThirtyFirstJuly->addYear();
                break;
            default:

        }

        return $nextThirtyFirstJuly;
    }


    /**
     * @param string $plan
     *
     * @return float|int
     */
    private function getTrialPrice(string $plan)
    {
        $now = Carbon::now();
        $trialEndDate = $this->getStartDate($plan);

        $diffInMonths = $trialEndDate->diffInMonths($now);
        $diffInDays = $trialEndDate->diffInDays($now->addMonths($diffInMonths));

        return round($diffInMonths * self::PRICE_PER_MONTH[$plan] + $diffInDays * (self::PRICE_PER_MONTH[$plan] / 30)
            , 2);
    }


    /**
     * Estimate the price of the trial before the regular subscription billing cycles
     *
     * @param string $plan
     *
     * @return JsonResponse
     * @throws Http400InvalidPlanException
     */
    public function estimate(string $plan)
    {
        if (!in_array($plan, array_keys(self::PRICE_PER_MONTH))) {
            throw new Http400InvalidPlanException($plan);
        }
        return new JsonResponse([
            "price" => $this->getTrialPrice($plan),
            "end_date" => $this->getStartDate($plan)
        ]);
    }


    /**
     * Get a transaction by its id
     *
     * @param Request $request
     * @param string $subscriptionId
     *
     * @return JsonResponse
     * @throws Http401UnauthorizedException
     */
    public function find(Request $request, string $subscriptionId)
    {
        /** @var User $user */
        $user = $request->user();

        if (!$user->isSupport()) {
            throw new Http401UnauthorizedException();
        }

        $subscription = \Braintree_Subscription::find($subscriptionId);
        return new JsonResponse($subscription->jsonSerialize());
    }


    /**
     * Get the user subscriptions
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws Http400InvalidRequestException
     */
    public function mine(Request $request)
    {
        /** @var User $user */
        $user = $request->user();
        $user->requireBraintreeCustomer();

        return new JsonResponse($user->getBraintreeSubscriptions());
    }


    /**
     * Create a new subscriptions for a user
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     * @throws Http400InvalidRequestException
     */
    public function create(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->premium) {
            throw new Http400InvalidRequestException("User is already premium");
        }

        $this->validate($request, [
            "plan" => "required|in:" . implode(",", array_keys(self::PRICE_PER_MONTH)),
        ]);

        // Create user as Braintree customer if necessary
        if (!$user->isBraintreeCustomer()) {
            $this->validate($request, [
                "payment_method_nonce" => "required",
            ]);

            $user->createBraintreeCustomer($request->input("payment_method_nonce"));
        }

        // Now we assume that the user is a Braintree customer
        $plan = $request->input("plan");

        $data = [
            "id" => $this->generateTransactionId(),
            "planId" => $request->input("plan"),
            "neverExpires" => true,
            "paymentMethodToken" => $user->getBraintreeDefaultPaymentMethodToken()
        ];

        if ($plan === "v4_annually" || $plan === "v4_biannually") {
            // Add trial
            $data = array_merge($data, [
                "trialPeriod" => true,
                "trialDurationUnit" => "day",
                "trialDuration" => $this->getStartDate($plan)->diffInDays(Carbon::now()) + 1
            ]);

            // Charge for the trial
            $chargeResult = \Braintree_Transaction::sale([
                "amount" => $this->getTrialPrice($plan),
                "customerId" => $user->braintree_id,
                "descriptor" => self::BRAINTREE_DESCRIPTOR,
                "options" => [
                    "submitForSettlement" => true,
                    "paypal" => [
                        "description" => "Abonnement Premium du " . Carbon::now()->format("d/m/Y") . " au " .
                            $this->getStartDate($plan)->format("d/m/Y")
                    ]
                ]
            ]);

            if (!$chargeResult->success) {
                throw new Http400InvalidRequestException([
                    "errors" => $chargeResult->errors
                ]);
            }
        }

        /** @var \Braintree\Result\Successful|\Braintree\Result\Error $subscriptionResult */
        $subscriptionResult = \Braintree_Subscription::create($data);

        $user->forgetBraintreeCustomerCache(); // Forget cache about the user, as we updated his subscriptions

        if ($subscriptionResult->success) {
            return new JsonResponse(User::find($user->id));
        } else {
            throw new Http400InvalidRequestException([
                "errors" => $subscriptionResult->errors
            ]);
        }
    }


    /**
     * Cancel a subscription for a user
     *
     * @param Request $request
     * @param string $subscriptionId
     *
     * @return JsonResponse
     * @throws Http400InvalidRequestException
     * @throws Http401UnauthorizedException
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
            return new JsonResponse([
                "status" => "success"
            ]);
        } else {
            throw new Http400InvalidRequestException([
                "errors" => $subscriptionResult->errors
            ]);
        }
    }


    /**
     * Get transactions linked with a subscription
     *
     * @param Request $request
     * @param string $subscriptionId
     *
     * @return JsonResponse
     * @throws Http401UnauthorizedException
     */
    public function transactions(Request $request, string $subscriptionId)
    {
        /** @var User $user */
        $user = $request->user();

        if (!$user->isSupport()) {
            throw new Http401UnauthorizedException();
        }

        $subscription = \Braintree_Subscription::find($subscriptionId);

        return new JsonResponse($subscription->jsonSerialize()["transactions"]);
    }
}
