<?php

namespace App\Controllers;

use App\Events\NewSubscriptionEvent;
use App\Events\TransactionSuccessEvent;
use App\Exceptions\Http400InvalidPlanException;
use App\Exceptions\Http400InvalidRequestException;
use App\Exceptions\Http401UnauthorizedException;
use App\Exceptions\Http422ModelSaveException;
use App\Exceptions\Http422ModelValidationException;
use App\Models\FamilyPack;
use App\Models\User;
use App\Services\Braintree\FamilyPackUtils;
use App\Services\Braintree\Formatter;
use App\Services\Braintree\Models\Plan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Routing\Controller;
use Zend\Diactoros\Response\JsonResponse;

/**
 * Class SubscriptionController.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 *
 * @since     4.1.0
 *
 * @copyright Mathrix Education SA
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
     * @param int    $additionalChildren
     *
     * @throws Http400InvalidPlanException
     *
     * @return JsonResponse
     */
    public function estimate(string $planId, int $additionalChildren = 0)
    {
        if (!\in_array($planId, array_keys(self::PRICE_PER_MONTH), true)) {
            throw new Http400InvalidPlanException($planId);
        }

        $plan = Plan::findOrFail($planId);
        $data = [
            "planId" => $plan->id,
            "trialPrice" => $plan->trialPrice,
            "trialEndDate" => $plan->trialEndDate
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
     * @param Request $request        The request
     * @param string  $subscriptionId The Braintree subscription id
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
     * @throws Http400InvalidRequestException
     * @throws Http422ModelSaveException
     * @throws Http422ModelValidationException
     * @throws ValidationException
     *
     * @return JsonResponse
     */
    public function create(Request $request)
    {
        // Validate the request
        $this->validate($request, [
            "planId" => "required|in:" . implode(",", array_keys(self::PRICE_PER_MONTH)),
            "family.*" => "exists:users,email",
        ]);

        // Prepare the working variables
        /** @var User $user */
        $user = $request->user();
        $user->append(["premium", "braintree"]);

        // Check if user is already premium
        if ($user->premium) {
            throw new Http400InvalidRequestException("User is already premium");
        }

        // Create user as Braintree customer if necessary
        if (!$user->isBraintreeCustomer()) {
            $this->validate($request, [
                "paymentMethodNonce" => "required", // Now paymentMethodNonce is required
            ]);

            $user->createBraintreeCustomer($request->input("paymentMethodNonce"));
        }

        // Now we assume that the user is a Braintree customer

        $plan = $request->input("planId");
        $data = [
            "id" => $this->generateSubscriptionId(),
            "planId" => $request->input("planId"),
            "neverExpires" => true,
            "paymentMethodToken" => $user->getBraintreeDefaultPaymentMethodToken(),
        ];

        // Initial checks for family-pack requests
        $familyEmails = $request->input("family", []);
        $isFamilyPack = !empty($familyEmails);

        if ($isFamilyPack) {
            $family = User::whereIn("email", $familyEmails)
                ->get(); // Retrieve the other users

            /** @var User $member */
            foreach ($family as $member) {
                $member->append(["premium", "braintree"]);

                if ($member->premium) {
                    throw new Http400InvalidRequestException("User with email `{$member->email}` is already premium");
                }
            }

            // We assume that everything is ok for family-pack
            $data["addOns"] = [
                "add" => FamilyPackUtils::getAddOns($plan, \count($familyEmails)),
            ];
        }

        if ($plan === "v4_annually" || $plan === "v4_biannually") {
            // Add trial
            $data = array_merge($data, [
                "trialPeriod" => true,
                "trialDurationUnit" => "day",
                "trialDuration" => $this->getStartDate($plan)
                        ->diffInDays(Carbon::now()) + 1,
            ]);

            $description = "Abonnement premium du " . Carbon::now()
                    ->format("d/m/Y") . " au " .
                $this->getStartDate($plan)
                    ->format("d/m/Y");
            $trialPrice = $this->getTrialPrice($plan);

            $initialSaleData = [
                "amount" => $trialPrice,
                "customerId" => $user->braintree_id,
                "lineItems" => [
                    [
                        "description" => $description,
                        "kind" => "debit",
                        "name" => "Abonnement premium",
                        "quantity" => 1,
                        "unitAmount" => $trialPrice,
                        "totalAmount" => $trialPrice,
                    ],
                ],
                "options" => [
                    "submitForSettlement" => true,
                    "paypal" => [
                        "description" => $description,
                    ],
                ],
            ];

            // Handle family pack additional price
            if ($isFamilyPack) {
                $initialSaleData["lineItems"] = array_merge(
                    $initialSaleData["lineItems"],
                    FamilyPackUtils::getAddOnsAsLineItemsForTrial($plan, \count($familyEmails))
                );

                $totalPrice = $trialPrice;
                foreach ($initialSaleData["lineItems"] as $lineItem) {
                    $totalPrice += $lineItem["totalAmount"];
                }

                $initialSaleData["amount"] = $totalPrice;
            }
            // Charge for the trial
            $chargeResult = \Braintree\Transaction::sale($initialSaleData);

            if ($chargeResult->success) {
                // Handle family pack membership grant
                if ($isFamilyPack) {
                    /** @var User $child */
                    foreach ($family as $child) {
                        $familyPack = new FamilyPack([
                            "parent_id" => $user->id,
                            "child_id" => $child->id,
                        ]);
                        $familyPack->save();
                    }
                }

                event(new TransactionSuccessEvent($chargeResult->transaction));
            } else {
                throw new Http400InvalidRequestException($chargeResult->message);
            }
        }

        /** @var \Braintree\Result\Successful|\Braintree\Result\Error $subscriptionResult */
        $subscriptionResult = \Braintree_Subscription::create($data);

        // Forget cache about the user, as we updated his subscriptions
        $user->forgetBraintreeCustomerCache();

        if ($subscriptionResult->success) {
            /** @var \Braintree_Subscription $subscription */
            $subscription = $subscriptionResult->subscription;

            // Raise event
            event(new NewSubscriptionEvent($user, $subscription));

            return new JsonResponse(
                Formatter::cast($subscription->jsonSerialize())
            );
        }
        $errors = [];

        /** @var \Braintree_Error_Validation $error */
        foreach ($subscriptionResult->errors->deepAll() as $error) {
            $errors[$error->attribute] = $error->message;
        }

        throw new Http400InvalidRequestException($subscriptionResult->message, 400, null, [
            "errors" => $errors,
        ]);
    }

    /**
     * Cancel a subscription for a user.
     *
     * @param Request $request        The request
     * @param string  $subscriptionId The Braintree subscription id
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

            $customerResultSearch = \Braintree_Customer::search([
                \Braintree_CustomerSearch::paymentMethodToken()
                    ->is($subscription->paymentMethodToken),
            ]);

            $customer = User::whereBraintreeId($customerResultSearch->firstItem()->id)
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
     * @param int     $userId  The user id
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

        return round($diffInMonths * self::PRICE_PER_MONTH[$plan] + $diffInDays * (self::PRICE_PER_MONTH[$plan] / 30), 2);
    }

    /**
     * Get the start date for a given plan.
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
        } elseif ((new Carbon("1st May"))->isPast()) {
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
     * Generate an unique transaction id based on an uuid and the prefix sub_.
     *
     * @return string
     */
    private function generateSubscriptionId(): string
    {
        return "sub_" . str_replace("-", "", \Faker\Factory::create()->uuid);
    }
}
