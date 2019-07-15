<?php


namespace App\Controllers\Processes;


use App\Exceptions\BraintreeErrorHandler;
use App\Exceptions\DuplicatePaymentMethodNonceException;
use App\Exceptions\GatewayRejectionException;
use App\Exceptions\ImmutableException;
use App\Exceptions\InvalidCustomerException;
use App\Exceptions\ProcessorDeclinedException;
use App\Exceptions\UnknownBraintreeException;
use App\Exceptions\UserAlreadyPremiumException;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use App\Services\Braintree\BraintreeResponse;
use App\Services\Braintree\Utils\SubscriptionUtils;
use Braintree\CreditCard;
use Braintree\PaymentMethod;
use Braintree\PayPalAccount;
use Braintree\Subscription as BraintreeSubscription;
use Braintree\Transaction as BraintreeTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Class SubscriptionCreationProcess.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA
 * @since 5.2.0
 */
class SubscriptionCreationProcess extends BaseProcess
{
    /** @var Request $request */
    private $request;
    /** @var string $planId */
    private $planId;
    /** @var User $user */
    private $user;
    /** @var CreditCard|PayPalAccount $paymentMethod */
    private $paymentMethod;
    /** @var BraintreeSubscription $subscription */
    private $subscription;
    /** @var BraintreeTransaction $initialTransaction */
    private $initialTransaction;


    public function __construct(Request $request = null, User $user = null)
    {
        $this->request = $request ?? app(Request::class);
        $this->user = $user ?? $this->request->user();
    }


    protected function validate(): void
    {
        // Check if the user is already premium
        if ($this->user->premium) {
            throw new UserAlreadyPremiumException($this->user);
        }

        // Make validation rules
        $rules = [
            "planId" => "required|in:" . implode(",", SubscriptionUtils::PLANS)
        ];

        if (!$this->user->canPay()) {
            // If the user has no default payment method, a nonce is required
            $rules["payment_method_nonce"] = "required";
        }

        Validator::make($this->request->all(), $rules);
        $this->planId = $this->request->json("plan_id");
    }


    /**
     * @throws GatewayRejectionException
     * @throws ImmutableException
     * @throws InvalidCustomerException
     * @throws ProcessorDeclinedException
     * @throws UnknownBraintreeException
     */
    protected function execute(): void
    {
        $this->user->lock();
        $this->selectPaymentMethod();
        $this->createSubscription();
        $this->createInitialTransaction();
        $this->user->unlock();
    }


    protected function finalize(): void
    {
        Subscription::createFromBraintreeSubscription($this->subscription, $this->user);
        Transaction::createFromBraintreeTransaction($this->initialTransaction);
    }


    /**
     * @throws GatewayRejectionException
     * @throws ImmutableException
     * @throws ProcessorDeclinedException
     * @throws UnknownBraintreeException
     */
    private function selectPaymentMethod()
    {
        if (!$this->user->isCustomer(true)) {
            /**
             * User is not a Braintree customer. A valid payment method nonce is required in order to define the new
             * customer's default payment method.
             */
            $this->user->createBraintreeCustomer($this->request->json("paymentMethodNonce"));

            $this->paymentMethod = $this->user->defaultPaymentMethod();
        } else if ($this->request->has("paymentMethodNonce")) {
            /*
             * User is already a Braintree customer but a paymentMethodNonce was passed: it means that the user want to
             * use another payment method. We will automatically set this new payment method as default for future
             * payments.
             */

            /** @var BraintreeResponse $paymentMethodCreation */
            $paymentMethodCreation = PaymentMethod::create([
                "customerId" => $this->user->braintree_id,
                "paymentMethodNonce" => $this->request->json("paymentMethodNonce"),
                "options" => [
                    "makeDefault" => true
                ]
            ]);

            if (!$paymentMethodCreation->success) {
                $this->user->record("braintree", "payment_method_creation", "error",
                    $paymentMethodCreation->jsonSerialize());
                BraintreeErrorHandler::report($paymentMethodCreation);
            }

            $this->paymentMethod = $paymentMethodCreation->paymentMethod;
        } else {
            /*
             * User is already a Braintree customer and no paymentMethodNonce was passed: we will use its default
             * payment method.
             */
            $this->paymentMethod = $this->user->defaultPaymentMethod();
        }
    }


    /**
     * Crate the subscription itself.
     *
     * @throws GatewayRejectionException
     * @throws ProcessorDeclinedException
     * @throws UnknownBraintreeException
     */
    private function createSubscription(): void
    {
        $subscriptionPayload = [
            "merchantAccountId" => env("BRAINTREE_MERCHANT_ACCOUNT"),
            "id" => "sub_" . str_replace("-", "", uuid()),
            "planId" => $this->request->json("planId"),
            "neverExpires" => true,
            "paymentMethodToken" => $this->paymentMethod->token
        ];

        if (in_array($this->request->json("plan_id"), [SubscriptionUtils::TRIAL_PLANS])) {
            $subscriptionPayload = array_replace($subscriptionPayload, [
                "trial" => true,
                "trialDuration" => SubscriptionUtils::getTrialDuration($this->request->json("plan_id")),
                "trialDurationUnit" => "day",
            ]);
        }


        /** @var BraintreeResponse $subscriptionCreationResult */
        $subscriptionCreationResult = gateway()->subscription()->create($subscriptionPayload);

        if (!$subscriptionCreationResult->success) {
            BraintreeErrorHandler::report($subscriptionCreationResult);
        }

        $this->subscription = $subscriptionCreationResult->subscription;
    }


    /**
     * Create the initial transaction for "Trial subscriptions".
     *
     * @throws GatewayRejectionException
     * @throws ProcessorDeclinedException
     * @throws UnknownBraintreeException
     */
    private function createInitialTransaction(): void
    {
        if (!SubscriptionUtils::isTrialPlan($this->planId)) {
            return; // Not needed
        }

        $amount = SubscriptionUtils::getTrialPeriodPrice($this->planId);
        $description = "Abonnement premium du " . Carbon::now()->format("d/m/Y") . " au "
            . SubscriptionUtils::getTrialEndDate($this->planId)->format("d/m/Y");

        $transactionPayload = [
            "merchantAccountId" => env("BRAINTREE_MERCHANT_ACCOUNT"),
            "amount" => $amount,
            "customerId" => $this->user->braintree_id,
            "paymentMethodToken" => $this->paymentMethod->token,
            "lineItems" => [
                [
                    "description" => $description,
                    "kind" => "debit",
                    "name" => "Abonnement premium",
                    "quantity" => 1,
                    "unitAmount" => $amount,
                    "totalAmount" => $amount,
                ],
            ],
            "options" => [
                "submitForSettlement" => true,
                "paypal" => [
                    "description" => $description,
                ],
            ],
        ];

        /** @var BraintreeResponse $transactionResult */
        $transactionResult = gateway()->transaction()->sale($transactionPayload);

        if (!$transactionResult->success) {
            /** @var BraintreeResponse $subscriptionResult */
            $subscriptionResult = gateway()->subscription()->cancel($this->subscription->id);
            // TODO: Handle subscription cancellation failure
            BraintreeErrorHandler::report($transactionResult);
        }

        $this->initialTransaction = $transactionResult->transaction;
    }
}
