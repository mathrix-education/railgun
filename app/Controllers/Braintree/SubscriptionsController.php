<?php
namespace App\Controllers\Braintree;

use App\Exceptions\Http400InvalidRequestException;
use App\Models\User;
use Carbon\Carbon;
use Faker\Factory;
use Illuminate\Http\Request;
use Zend\Diactoros\Response\JsonResponse;

/**
 * Class SubscriptionsController
 *
 * @author Mathrix Back-End Team
 * @package App\Controllers\Braintree
 * @since 4.1.0
 * @copyright Mathrix SA
 */
class SubscriptionsController extends BraintreeController
{
    private const PRICE_PER_MONTH = [
        "v4_monthly" => 17.90,
        "v4_annually" => 12.90,
        "v4_biannually" => 9.90
    ];


    /**
     * Generate anb unique transaction id based on an uuid and the prefix sub_
     *
     * @return string
     * @throws \Exception
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


    public function find(Request $request, string $subscriptionId)
    {

    }


    /**
     * Create a new subscriptions for a user
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Exception
     */
    public function create(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

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
                "trialDuration" => $this->getStartDate($plan)->diffInDays(Carbon::now())
            ]);

            // Charge for the trial
            $chargeResult = $this->getGateway()->transaction()->sale([
                "amount" => $this->getTrialPrice($plan),
                "customerId" => $user->braintree_id,
                "options" => [
                    "submitForSettlement" => true
                ]
            ]);

            if (!$chargeResult->success) {
                throw new Http400InvalidRequestException([
                    "errors" => $chargeResult->errors
                ]);
            }
        }

        /** @var \Braintree\Result\Successful|\Braintree\Result\Error $subscriptionResult */
        $subscriptionResult = $this->getGateway()
            ->subscription()
            ->create($data);

        $user->forgetBraintreeCustomerCache(); // Forget cache about the user, as we updated his subscriptions

        if ($subscriptionResult->success) {
            return new JsonResponse(User::find($user->id));
        } else {
            throw new Http400InvalidRequestException([
                "errors" => $subscriptionResult->errors
            ]);
        }
    }


    public function cancel(Request $request, string $subscriptionId)
    {

    }
}
