<?php
namespace App\Controllers;

use App\Exceptions\Http400InvalidRequestException;
use App\Exceptions\Http422ModelSaveException;
use App\Exceptions\Http422ModelValidationException;
use App\Models\Log;
use App\Models\User;
use App\Utils\StripeUtils;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Laravel\Cashier\Http\Controllers\WebhookController as CashierController;
use Laravel\Cashier\Subscription;

/**
 * Class StripeController
 *
 * @author Mathrix Back-End Team
 * @package App\Controllers
 * @since 1.0.0
 * @copyright Mathrix Education SA
 */
class StripeController extends CashierController
{
    /**
     * @apiVersion 4.0.0
     * @api {post} /stripe/subscribe Subscribe
     * @apiGroup Stripe
     * @apiName StripeSubscribe
     * @apiDescription Subscribe user to plan
     * @apiPermission registered
     * @apiUse Bearer
     * @apiParam (Data) {string=v3_biannually,v3_annually,v3_monthly} stripe_plan The stripe plan
     * @apiParam (Data) {string} [stripe_token] The stripe token; can be null if a coupon is given
     * @apiParam (Data) {string} [stripe_coupon=null] The stripe coupon
     * @apiUse UserModel
     * @apiError (Error 400) invalid_request stripe_token and stripe_coupon cannot be null at the same time
     * @apiError (Error 400) invalid_request stripe_plan has to be in the allowed stripe plans
     * @apiError (Error 400) invalid_request `card invalid_number` The card number is not a valid credit card number<br>
     *  `invalid_expiry_month` The card's expiration month is invalid<br>
     *  `invalid_expiry_year` The card's expiration year is invalid<br>
     *  `invalid_cvc` The card's security code is invalid<br>
     *  `invalid_swipe_data` The card's swipe data is invalid<br>
     *  `incorrect_number` The card number is incorrect<br>
     *  `expired_card` The card has expired<br>
     *  `incorrect_cvc` The card's security code is incorrect<br>
     *  `incorrect_zip` The card's zip code failed validation<br>
     *  `card_declined` The card was declined<br>
     *  `missing` There is no card on a customer that is being charged<br>
     *  `processing_error` An error occurred while processing the card
     *
     * @param Request $request
     *
     * @return Response
     * @throws Http400InvalidRequestException
     * @throws Http422ModelSaveException
     * @throws Http422ModelValidationException
     * @throws \Exception
     */
    public function subscribe(Request $request)
    {
        $stripeToken = $request->input("stripe_token");
        /** @var string $stripePlan */
        $stripePlan = $request->input("stripe_plan");
        $stripeCoupon = $request->input("stripe_coupon");

        if ($stripeToken === null && $stripeCoupon === null) {
            throw new Http400InvalidRequestException("stripe_token and stripe_coupon cannot be both null");
        }

        if (!in_array($stripePlan, array_keys(StripeUtils::ALLOWED_STRIPE_PLANS))) {
            throw new Http400InvalidRequestException("$stripePlan is not a valid Stripe plan");
        }

        /** @var User $user */
        $user = $request->user();
        if ($stripePlan === null || $stripePlan === "v3_monthly") {
            // Coupon only or monthly, normal process
            $user->newSubscription("premium", $stripePlan)
                ->withCoupon($stripeCoupon)
                ->create($stripeToken);
        } else {
            // v3_biannually or v3_annually
            $user->updateCardOrCreateStripeCustomer($stripeToken);

            // Pipe Stripe exception to our HTTP 400 Bad Request exception
            try {
                $description = StripeUtils::ALLOWED_STRIPE_PLANS[$stripePlan]["name"] . " (fin " .
                    StripeUtils::getStripePlanEndDate($stripePlan)->format("d/m/Y") . ")";
                $response = $user->charge(StripeUtils::getStripePlanPrice($stripePlan), [
                    "description" => $description
                ]);
            } catch (\InvalidArgumentException $exception) {
                throw new Http400InvalidRequestException($exception->getMessage());
            }

            // Give subscription if charge succeeded
            if ($response->status == "succeeded") {
                $user->newSubscription("premium", $stripePlan)
                    ->withCoupon($stripeCoupon)
                    ->trialUntil(StripeUtils::getStripePlanEndDate($stripePlan))
                    ->create($stripeToken);
                Log::log("user_payment_succeed_for_premium", $user);
            } else {
                Log::log("user_payment_failed_for_premium", $user);
            }
        }

        return response()->json(User::with("subscriptions")->find($user->id));
    }


    /**
     * @apiVersion 4.0.0
     * @api {get} /stripe/estimate/{stripe_plan} Estimate
     * @apiGroup Stripe
     * @apiName StripeEstimate
     * @apiDescription Estimate the price for a plan relatively to the current date
     * @apiPermission public
     * @apiParam {string=v3_biannually,v3_annually} stripe_plan The stripe plan
     * @apiSuccess {int} price The price if the user orders today
     * @apiSuccess {string} end_date The ending date (Y-m-d)
     * @apiError (Error 400) invalid_request stripe_plan has to be in the allowed stripe plans
     *
     * @param string $stripePlan
     *
     * @return JsonResponse
     * @throws Http400InvalidRequestException
     * @throws \Exception
     */
    public function estimate(string $stripePlan)
    {
        return response()->json([
            "price" => StripeUtils::getStripePlanPrice($stripePlan),
            "end_date" => StripeUtils::getStripePlanEndDate($stripePlan)->format("Y-m-d")
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Stripe webhooks
    |--------------------------------------------------------------------------
    */


    /**
     * Payment succeeded
     *
     * @param  array $payload
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws Http400InvalidRequestException
     * @throws Http422ModelSaveException
     * @throws Http422ModelValidationException
     * @throws \Exception
     */
    public function handleInvoicePaymentSucceeded($payload)
    {
        $user = $this->getUserByStripeId($payload["data"]["object"]["customer"]);

        if ($user) {
            Log::log("user_payment_succeed_for_premium", $user);
        }

        return new Response('Webhook Handled', 200);
    }


    /**
     * Payment failed
     *
     * @param  array $payload
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws Http400InvalidRequestException
     * @throws Http422ModelSaveException
     * @throws Http422ModelValidationException
     * @throws \Exception
     */
    public function handleInvoicePaymentFailed(array $payload)
    {
        $user = $this->getUserByStripeId($payload["data"]["object"]["customer"]);

        if ($user) {
            Log::log("user_payment_failed_for_premium", $user);

            // TODO: Send payment failed email
        }

        return new Response('Webhook Handled', 200);
    }


    /**
     * Handle customer.subscription.deleted
     *
     * @param array $payload
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws Http400InvalidRequestException
     * @throws Http422ModelSaveException
     * @throws Http422ModelValidationException
     * @throws \Exception
     */
    public function handleCustomerSubscriptionDeleted(array $payload)
    {
        /** @var User $user */
        $user = $this->getUserByStripeId($payload["data"]["object"]["customer"]);

        if ($user) {
            $user->subscriptions->filter(function ($subscription) use ($payload) {
                /** @var Subscription $subscription */
                return $subscription->stripe_id === $payload['data']['object']['id'];
            })->each(function ($subscription) {
                /** @var Subscription $subscription */
                if ($subscription->onTrial()) {
                    $subscription->skipTrial();
                }

                $subscription->cancelNow();
            });
        }

        if ($user) {
            Log::log("user_cancelled_premium", $user);

            // TODO: Send cancellation email
        }

        return new Response('Webhook Handled', 200);
    }
}
