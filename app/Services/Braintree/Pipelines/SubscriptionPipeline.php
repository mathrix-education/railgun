<?php

namespace App\Services\Braintree\Pipelines;

use App\Events\NewSubscriptionEvent;
use App\Events\TransactionSuccessEvent;
use App\Exceptions\UserAlreadyPremiumException;
use App\Models\User;
use App\Services\Braintree\Exceptions\BraintreeErrorHandler;
use App\Services\Braintree\Exceptions\BraintreeProcessorException;
use App\Services\Braintree\Models\Plan;
use App\Services\Braintree\Utils\Formatter;
use App\Services\Braintree\Utils\SubscriptionUtils;
use Braintree\CreditCard;
use Braintree\PaymentMethod;
use Braintree\PayPalAccount;
use Braintree\Result\Error;
use Braintree\Result\Successful;
use Braintree\Subscription;
use Braintree\Transaction;
use Carbon\Carbon;
use Faker\Provider\Uuid;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Mathrix\Lumen\Exceptions\Http\Http400BadRequestException;

/**
 * Class SubscriptionPipeline.
 * Pipeline which creates a new Braintree subscription.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0
 */
class SubscriptionPipeline
{
    /** @var Request $request The original request. */
    private $request;
    /** @var string $planId The plan id. */
    private $planId;
    /**
     * @var bool $hasTrial If we should attempt to create a subscription with trial period and charge an initial
     * transaction for the trial period.
     */
    private $hasTrialPeriod;

    /** @var JsonResponse The response. */
    private $response;
    /** @var User $user */
    private $user;
    /** @var CreditCard|PayPalAccount $paymentMethod */
    private $paymentMethod;
    /** @var Subscription $subscription */
    private $subscription;
    /** @var Transaction $transaction */
    private $transaction;


    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->user = $request->user();
    }


    /**
     * @return JsonResponse
     * @throws BraintreeProcessorException
     * @throws Http400BadRequestException
     * @throws UserAlreadyPremiumException
     * @throws ValidationException
     * @throws \Mathrix\Lumen\Exceptions\ValidationException
     */
    public function run()
    {
        $this->validationCheck();
        $this->premiumCheck();

        $this->selectPaymentMethod();
        $this->createSubscription();
        $this->createInitialTransaction();
        $this->finalize();

        return $this->response;
    }


    /**
     * Check that the request payload is valid.
     * We do not record the exception.
     * @throws Http400BadRequestException
     * @throws ValidationException
     */
    private function validationCheck(): void
    {
        $rules = [
            "planId" => "required|in:" . implode(",", Arr::pluck(Plan::all()->toArray(), "id")),
            "error_code" => "integer"
        ];

        $validator = Validator::make($this->request->all(), $rules);

        if ($validator->fails()) {
            $validationException = new ValidationException($validator);
            $validationException->status(422);
            throw $validationException;
        }

        // Mock processor error
        if ($this->request->has("error_code")) {
            if (env("BRAINTREE_ENVIRONMENT") !== "sandbox") {
                throw new Http400BadRequestException(null, "error_code should not be used in non-sandbox environment!");
            }

            SubscriptionUtils::fake();
            SubscriptionUtils::$ForceTrialPrice = $this->request->json("error_code");
        }

        $this->planId = $this->request->input("planId");
        $this->hasTrialPeriod = $this->planId === "v4_annually" || $this->planId === "v4_biannually";
    }


    /**
     * Check that the user is not already premium.
     * We do not record the exception.
     * @throws UserAlreadyPremiumException
     */
    private function premiumCheck(): void
    {
        $this->user->append(["premium", "braintree"]);

        // Check if user is already premium
        if ($this->user->premium) {
            throw new UserAlreadyPremiumException();
        }
    }


    /**
     * @throws Http400BadRequestException
     * @throws \Mathrix\Lumen\Exceptions\ValidationException
     */
    private function selectPaymentMethod()
    {
        if (!$this->user->isBraintreeCustomer()) {
            /**
             * User is not a Braintree customer. A valid payment method nonce is required in order to define the new
             * customer's default payment method.
             */
            Validator::make($this->request->all(), ["paymentMethodNonce" => "required"]);

            $this->user->createBraintreeCustomer($this->request->input("paymentMethodNonce"));

            $this->paymentMethod = $this->user->braintree->defaultPaymentMethod();
        } else if ($this->request->has("paymentMethodNonce")) {
            /*
             * User is already a Braintree customer but a paymentMethodNonce was passed: it means that the user want to
             * use another payment method. We will automatically set this new payment method as default for future
             * payments.
             */
            /** @var Successful|Error $paymentMethodCreation */
            $paymentMethodCreation = PaymentMethod::create([
                "customerId" => $this->user->braintree->id,
                "paymentMethodNonce" => $this->request->input("paymentMethodNonce"),
                "options" => [
                    "makeDefault" => true
                ]
            ]);

            if (!$paymentMethodCreation->success) {
                $this->user->record("braintree", "payment_method_creation", "error",
                    $paymentMethodCreation->jsonSerialize());
            }

            $this->paymentMethod = $paymentMethodCreation->paymentMethod;
        } else {
            /*
             * User is already a Braintree customer and no paymentMethodNonce was passed: we will use its default
             * payment method.
             */
            $this->paymentMethod = $this->user->braintree->defaultPaymentMethod();
        }
    }


    /**
     * Now we assume that the user is valid Braintree customer with an attached payment method. We will now create the
     * subscription.
     * @throws Http400InvalidRequestException
     * @throws Http422ModelSaveException
     * @throws Http422ModelValidationException
     */
    private function createSubscription()
    {
        /** @var array $subscriptionPayload The subscription payload. */
        $subscriptionPayload = [
            "merchantAccountId" => env("BRAINTREE_MERCHANT_ACCOUNT"),
            "id" => "sub_" . str_replace("-", "", Uuid::uuid()),
            "planId" => $this->request->input("planId"),
            "neverExpires" => true,
            "paymentMethodToken" => $this->paymentMethod->token
        ];

        if ($this->hasTrialPeriod) {
            // Add trial parameters to subscription payload.
            $subscriptionPayload = array_merge($subscriptionPayload, [
                "trialPeriod" => true,
                "trialDurationUnit" => "day",
                "trialDuration" => SubscriptionUtils::getRegularPlanStartDate($this->planId)->diffInDays(Carbon::now()) + 1
            ]);
        }


        /**
         * @var Successful|Error $subscriptionCreation The subscription creation result. If the creation succeeded, we
         * can continue the process.
         */
        $subscriptionCreation = Subscription::create($subscriptionPayload);

        if ($subscriptionCreation->success) {
            /** @var Successful $subscriptionCreation */
            $this->subscription = $subscriptionCreation->subscription;
        } else {
            /** @var Error $subscriptionCreation */
            $this->user->record("braintree", "subscription_creation", "error",
                $subscriptionCreation->jsonSerialize());

            throw new Http400InvalidRequestException(
                "Subscription creation failed.",
                400,
                null,
                ["data" => $subscriptionCreation->jsonSerialize()]
            );
        }
    }


    /**
     * We now assume that the transaction succeeded. If the subscription requires a forced trial period, we create the
     * initial transaction.
     * @throws Http400InvalidRequestException
     * @throws Http422ModelSaveException
     * @throws Http422ModelValidationException
     * @throws BraintreeProcessorException
     */
    private function createInitialTransaction()
    {
        if (!$this->hasTrialPeriod) {
            // Immediately returns if there is no trial payment
            return;
        }

        $description = "Abonnement premium du " . Carbon::now()->format("d/m/Y") . " au " .
            SubscriptionUtils::getRegularPlanStartDate($this->planId)->format("d/m/Y");
        $trialPrice = SubscriptionUtils::getTrialPeriodPrice($this->planId);

        /**
         * @var array $initialTransactionPayload The initial transaction payload.
         */
        $initialTransactionPayload = [
            "merchantAccountId" => env("BRAINTREE_MERCHANT_ACCOUNT"),
            "amount" => $trialPrice,
            "customerId" => $this->user->braintree_id,
            "paymentMethodToken" => $this->paymentMethod->token,
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

        if ($initialTransactionSale->success) {
            $this->transaction = $initialTransactionSale->transaction;
        } else {
            // Initial transaction failed. We will now cancel the subscription.
            /** @var Successful|Error $subscriptionResult $subscriptionCancellationResult */
            $subscriptionCancellation = Subscription::cancel($this->subscription->id);

            if ($subscriptionCancellation->success) {
                $subscriptionCancellationStatus = "success";
                $subscriptionCancellationPayload = $subscriptionCancellation->subscription->jsonSerialize();
            } else {
                $subscriptionCancellationStatus = "error";
                $subscriptionCancellationPayload = $subscriptionCancellation->jsonSerialize();
            }

            $this->user->record(
                "braintree",
                "subscription_cancelled",
                $subscriptionCancellationStatus,
                $subscriptionCancellationPayload
            );

            BraintreeErrorHandler::report($initialTransactionSale);
        }
    }


    /**
     * The subscription creation succeeded and the initial transaction, if it was needed, did not failed. We can now
     * safely send our notifications to the user by dispatching the required events.
     */
    private function finalize()
    {
        // If we have an init
        if ($this->transaction instanceof Transaction) {
            // Send the success event for the initial transaction
            event(new TransactionSuccessEvent($this->transaction));
        }

        event(new NewSubscriptionEvent($this->user, $this->subscription));

        $this->user->forgetBraintreeCustomerCache(); // Forget cache about the user, as we updated his subscriptions

        $this->response = new JsonResponse(
            Formatter::cast($this->subscription->jsonSerialize())
        );
    }
}
