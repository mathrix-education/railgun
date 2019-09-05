<?php

namespace App\Controllers;

use App\Models\User;
use App\Services\Stripe\Exceptions\IncorrectCVCException;
use App\Services\Stripe\Exceptions\StripeHandler;
use App\Services\Stripe\SubscriptionUtils;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Mathrix\Lumen\Zero\Controllers\BaseController;
use Mathrix\Lumen\Zero\Exceptions\ValidationException;
use Mathrix\Lumen\Zero\Responses\SuccessJsonResponse;
use Stripe\Customer;
use Stripe\Error\Base as StripeBaseException;
use Stripe\PaymentIntent;
use Stripe\Subscription;

/**
 * Class SubscriptionsController.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 4.1.0
 */
class SubscriptionsController extends BaseController
{
    /**
     * POST /subscriptions
     * Create a new subscription.
     *
     * @param Request $request
     *
     * @return SuccessJsonResponse
     * @throws ValidationException
     * @throws IncorrectCVCException
     */
    public function standardPost(Request $request): SuccessJsonResponse
    {
        $this->validate($request, [
            "token" => "required",
            "plan" => "required|in:" . implode(",", array_keys(SubscriptionUtils::PLANS_DETAILS))
        ]);

        /** @var User $user */
        $user = $request->user();
        $customer = $user->asStripeCustomer(true);
        try {
            $customer = Customer::update($customer->id, [
                "source" => $request->json("token")
            ]);
        } catch (StripeBaseException $exception) {
            StripeHandler::throws($exception);
        }

        $plan = $request->json("plan");

        /**
         * @var PaymentIntent $paymentIntent
         * @var Subscription $subscription
         */
        if (SubscriptionUtils::isRegularPlan($plan)) {
            [$paymentIntent, $subscription] = $this->regular($user, $customer, $plan);
        } else {
            [$paymentIntent, $subscription] = $this->trial($user, $customer, $plan);
        }

        $data = [
            "initial_payment_intent" => $paymentIntent === null ? null : data_filter($paymentIntent->jsonSerialize(), [
                "client_secret",
                "status"
            ]),
            "subscription" => data_filter($subscription->jsonSerialize(), [
                "id",
                "latest_invoice.payment_intent.client_secret",
                "latest_invoice.payment_intent.status",
                "plan.id",
                "status"
            ])
        ];

        return new SuccessJsonResponse($data);
    }


    /**
     * Create a regular subscription.
     *
     * @param User $user
     * @param Customer $customer
     * @param string $plan
     *
     * @return array
     */
    private function regular(User $user, Customer $customer, string $plan): array
    {
        // Only create the subscription since a regular plan does not require an initial payment intent.
        $subscription = Subscription::create([
            "customer" => $customer->id,
            "items" => [
                ["plan" => $plan]
            ],
            "metadata" => [
                "user_id" => $user->id,
            ],
            "expand" => ["latest_invoice.payment_intent"]
        ]);

        return [null, $subscription];
    }


    /**
     * Create the initial payment intent and the subscription.
     *
     * @param User $user
     * @param Customer $customer
     * @param string $plan
     *
     * @return array
     */
    private function trial(User $user, Customer $customer, string $plan): array
    {
        // Create the initial payment intent
        $paymentIntent = PaymentIntent::create([
            "amount" => SubscriptionUtils::getTrialDuration($plan),
            "currency" => "eur",
            "confirm" => true,
            "customer" => $user->stripe_id,
            "description" => "Abonnement au Mode Machine du " . Carbon::now()->format("d/m/Y") . " au "
                . SubscriptionUtils::getTrialEndDate($plan)->format("d/m/Y"),
            "metadata" => [
                "user_id" => $user->id
            ],
            "payment_method" => $customer->default_source,
            "receipt_email" => $user->email,
            "save_payment_method" => true,
            "setup_future_usage" => "off_session"
        ]);

        // Then, create the subscription
        $subscription = Subscription::create([
            "customer" => $customer->id,
            "items" => [
                ["plan" => $plan]
            ],
            "metadata" => [
                "user_id" => $user->id,
                "initial_payment_intent" => $paymentIntent->id
            ],
            "trial_period_days" => SubscriptionUtils::getTrialDuration($plan),
            "expand" => ["latest_invoice.payment_intent"]
        ]);

        // Add the subscription id to the payment intent metadata
        $paymentIntent = PaymentIntent::update($paymentIntent->id, [
            "metadata" => [
                "user_id" => $user->id,
                "subscription_id" => $subscription->id
            ],
        ]);

        return [$paymentIntent, $subscription];
    }
}
