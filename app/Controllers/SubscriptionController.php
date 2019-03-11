<?php
namespace App\Controllers;

use App\Events\NewSubscriptionEvent;
use App\Events\TransactionSuccessEvent;
use App\Exceptions\Http400InvalidPlanException;
use App\Exceptions\Http400InvalidRequestException;
use App\Exceptions\Http401UnauthorizedException;
use App\Models\User;
use App\Services\Braintree\FamilyPackUtils;
use App\Services\Braintree\Formatter;
use App\Services\Braintree\Models\Plan;
use Braintree\PaymentMethod;
use Braintree\Result\Error;
use Braintree\Result\Successful;
use Braintree\Subscription;
use Braintree\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Routing\Controller;
use Zend\Diactoros\Response\JsonResponse;

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
     * @param int    $additionalChildren
     *
     * @throws Http400InvalidPlanException
     *
     * @return JsonResponse
     */
    public function estimate(string $planId, $additionalChildren = 0)
    {
        if (!\in_array($planId, array_keys(self::PRICE_PER_MONTH), true)) {
            throw new Http400InvalidPlanException($planId);
        } elseif ($planId === "v4_monthly") {
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
     * @return JsonResponse
     * @throws Http400InvalidRequestException
     * @throws ValidationException
     */
    public function create(Request $request)
    {
        // Validate the request
        $this->validate($request, [
            "planId" => "required|in:" . implode(",", array_keys(self::PRICE_PER_MONTH)),
        ]);

        // Prepare the working variables
        /** @var User $user */
        $user = $request->user();
        $user->append(["premium", "braintree"]);

        // Check if user is already premium
        if ($user->premium) {
            throw new Http400InvalidRequestException("User is already premium");
        }

        /*
        |--------------------------------------------------------------------------
        | Payment method selection
        |--------------------------------------------------------------------------
        | We need to select a payment method based on the given request. If the user is a not already a valid Braintree
        | customer, a paymentMethodNonce is REQUIRED in order to attach a default payment method to the newly created
        | Braintree customer account.
        | If the user is already a valid Braintree customer, and provides a paymentMethodNonce, we will change his
        | default payment to the one linked with the paymentMethodNonce. Else, we will use his default payment method.
        */
        // Create user as Braintree customer if necessary
        if (!$user->isBraintreeCustomer()) {
            /**
             * User is not a Braintree customer. A valid payment method nonce is required in order to define the new
             * customer's default payment method.
             */
            $this->validate($request, [
                "paymentMethodNonce" => "required", // Now paymentMethodNonce is required
            ]);

            $user->createBraintreeCustomer($request->input("paymentMethodNonce"));

            $paymentMethod = $user->braintree->defaultPaymentMethod();
        } elseif ($request->has("paymentMethodNonce")) {
            /*
             * User is already a Braintree customer but a paymentMethodNonce was passed: it means that the user want to
             * use another payment method. We will automatically set this new payment method as default for future
             * payments.
             */
            $paymentMethodCreation = PaymentMethod::create([
                "customerId" => $user->braintree->id,
                "paymentMethodNonce" => $request->input("paymentMethodNonce"),
                "options" => [
                    "makeDefault" => true
                ]
            ]);

            $paymentMethod = $paymentMethodCreation->paymentMethod;
        } else {
            /*
             * User is already a Braintree customer and no paymentMethodNonce was passed: we will use its default
             * payment method.
             */
            $paymentMethod = $user->braintree->defaultPaymentMethod();
        }

        /*
        |--------------------------------------------------------------------------
        | Subscription creation.
        |--------------------------------------------------------------------------
        | Now we assume that the user is valid Braintree customer with an attached payment method.
        | We will now create the subscription.
        */
        $plan = $request->input("planId");
        /**
         * @var bool $hasTrialPeriod If we should attempt to create a subscription with trial period and charge an
         * initial transaction for the trial period.
         */
        $hasTrialPeriod = $plan === "v4_annually" || $plan === "v4_biannually";
        /**
         * @var array $subscriptionPayload The subscription payload.
         */
        $subscriptionPayload = [
            "merchantAccountId" => env("BRAINTREE_MERCHANT_ACCOUNT"),
            "id" => $this->generateSubscriptionId(),
            "planId" => $request->input("planId"),
            "neverExpires" => true,
            "paymentMethodToken" => $paymentMethod->token,
        ];

        if ($hasTrialPeriod) {
            // Add trial parameters to subscription payload.
            $subscriptionPayload = array_merge($subscriptionPayload, [
                "trialPeriod" => true,
                "trialDurationUnit" => "day",
                "trialDuration" => $this->getStartDate($plan)
                        ->diffInDays(Carbon::now()) + 1,
            ]);
        }
        /**
         * @var Successful|Error $subscriptionCreation The subscription creation
         * result. If the creation succeeded, we can continue the process.
         */
        $subscriptionCreation = \Braintree_Subscription::create($subscriptionPayload);

        if (!$subscriptionCreation->success) {
            throw new Http400InvalidRequestException(
                "Subscription creation failed.",
                400,
                null,
                ["data" => $subscriptionCreation->jsonSerialize()]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Trial transaction
        |--------------------------------------------------------------------------
        | We now assume that the transaction succeeded. If the subscription requires a forced trial period, we create
        | the initial transaction.
        */
        if ($hasTrialPeriod) {
            $description = "Abonnement premium du " . Carbon::now()->format("d/m/Y") . " au " .
                $this->getStartDate($plan)->format("d/m/Y");
            $trialPrice = $this->getTrialPrice($plan);

            $initialTransactionPayload = [
                "merchantAccountId" => env("BRAINTREE_MERCHANT_ACCOUNT"),
                "amount" => $trialPrice,
                "customerId" => $user->braintree_id,
                "paymentMethodToken" => $paymentMethod->token,
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

            // Charge for the trial
            $initialTransactionSale = Transaction::sale($initialTransactionPayload);

            if (!$initialTransactionSale->success) {
                /*
                 * Initial transaction failed. We will now revert everything, including, in this order:
                 * 1. Cancel the subscription.
                 * 2. Remove the payment method?
                 */
                /** @var Subscription $subscription The created subscription. */
                $subscription = $subscriptionCreation->subscription;
                /** @var Successful|Error $subscriptionResult $subscriptionCancellationResult */
                $subscriptionCancellationResult = Subscription::cancel($subscription->id);

                throw new Http400InvalidRequestException(
                    "Initial transaction failed.",
                    400,
                    null,
                    ["data" => $initialTransactionSale->jsonSerialize()]
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Finalisation of the process
        |--------------------------------------------------------------------------
        | The subscription creation succeeded and the initial transaction, if it was needed, did not failed. We can now
        | safely send our notifications to the user by dispatching the required events.
        */
        if (isset($initialTransactionSale) && $initialTransactionSale->success) {
            // Send the success event for the initial transaction
            event(new TransactionSuccessEvent($initialTransactionSale->transaction));
        }

        /** @var Subscription $subscription */
        $subscription = $subscriptionCreation->subscription;
        event(new NewSubscriptionEvent($user, $subscription));

        $user->forgetBraintreeCustomerCache(); // Forget cache about the user, as we updated his subscriptions

        return new JsonResponse(
            Formatter::cast($subscription->jsonSerialize())
        );
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

            $customerResultSearch = \Braintree\Customer::search([
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
