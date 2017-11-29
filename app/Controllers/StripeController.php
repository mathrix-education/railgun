<?php
namespace App\Controllers;

use App\Exceptions\Http400InvalidRequestException;
use App\Exceptions\Http422ModelSaveException;
use App\Exceptions\Http422ModelValidationException;
use App\Models\Log;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Laravel\Cashier\Http\Controllers\WebhookController as CashierController;

/**
 * Class StripeController
 *
 * @author Mathrix Back-End Team
 * @package App\Controllers
 * @since 1.0.0
 * @copyright Mathrix
 */
class StripeController extends CashierController
{
    private const ALLOWED_STRIPE_PLANS = [
        "v3_biannually" => [
            "price_per_month" => 9.90,
            "price_per_day" => 0.33
        ],
        "v3_annually" => [
            "price_per_month" => 12.90,
            "price_per_day" => 0.43
        ],
        "v3_monthly" => [
            "price_per_month" => 17.90,
            "price_per_day" => 0.60
        ]
    ];


    /**
     * @apiVersion 1.0.0
     * @api {post} /stripe/subscribe Subscribe
     * @apiGroup Stripe
     * @apiName StripeSubscribe
     * @apiDescription Subscribe user to plan
     * @apiPermission registered
     * @apiUse Bearer
     * @apiParam (Data) {string=v3_biannually,v3_annually,v3_monthly} stripe_plan The stripe plan
     * @apiParam (Data) {string} [stripe_token] The stripe token; can be null if a coupon is given
     * @apiParam (Data) {string} [stripe_coupon=null] The stripe coupon
     * @apiUse User
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
     */
    public function subscribe(Request $request)
    {
        $stripeToken = $request->input("stripe_token");
        $stripePlan = $request->input("stripe_plan");
        $stripeCoupon = $request->input("stripe_coupon");

        if ($stripeToken === null && $stripeCoupon === null) {
            throw new Http400InvalidRequestException("stripe_token and stripe_coupon cannot be both null");
        }

        if (!in_array($stripePlan, array_keys(self::ALLOWED_STRIPE_PLANS))) {
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
            $user->newSubscription("premium", $stripePlan)
                ->withCoupon($stripeCoupon)
                ->trialUntil($this->getEndDateFromStripePlan($stripePlan))
                ->create($stripeToken);

            // Charge the user
            $response = $user->charge($this->getPriceFromStripePlan($stripePlan));

            // Cancel subscription if charge is pending or failed
            if ($response->status !== "succeeded") {
                $user->subscription("premium")->cancelNow();
            }
        }

        Log::log("user_payment_succeed_for_premium", $user);

        return response()->json(User::with("subscriptions")->find($user->id));
    }


    /**
     * @apiVersion 1.0.0
     * @api {get} /stripe/estimate/{stripe_plan} Estimate
     * @apiGroup Stripe
     * @apiName StripeEstimate
     * @apiDescription Estimate the price for a plan relatively to the current date
     * @apiPermission public
     * @apiParam {string=v3_biannually,v3_annually} stripe_plan The stripe plan
     * @apiSuccess {int} price The price if the user orders today
     * @apiError (Error 400) invalid_request stripe_plan has to be in the allowed stripe plans
     *
     * @param string $stripePlan
     *
     * @return JsonResponse
     * @throws Http400InvalidRequestException
     */
    public function estimate(string $stripePlan)
    {
        return response()->json([
            "price" => $this->getPriceFromStripePlan($stripePlan)
        ]);
    }


    /**
     * @param string $stripePlan
     *
     * @return Carbon
     * @throws Http400InvalidRequestException
     */
    private function getEndDateFromStripePlan(string $stripePlan)
    {
        // Calculate the next July (ie. the ending date)
        $nextJuly = Carbon::create(null, 7, 31);
        switch ($stripePlan) {
            case "v3_biannually":
                $nextJuly->addYears($nextJuly->isNextYear() ? 1 : 2);
                break;
            case "v3_annually":
                if (!$nextJuly->isNextYear()) {
                    $nextJuly->addYear();
                }
                break;
            default:
                throw new Http400InvalidRequestException("$stripePlan is not a valid estimable plan");
                break;
        }

        return $nextJuly;
    }


    /**
     * Get the price of a Stripe plan based on the current date
     *
     * @param string $stripePlan
     *
     * @return int The price, in cents
     * @throws Http400InvalidRequestException
     */
    private function getPriceFromStripePlan(string $stripePlan)
    {
        $now = Carbon::now();
        $nextJuly = $this->getEndDateFromStripePlan($stripePlan);

        $months = $nextJuly->diffInMonths($now);
        $days = $nextJuly->diffInDays($now->addMonths($months));

        $pricePerMonth = self::ALLOWED_STRIPE_PLANS[$stripePlan]["price_per_month"];
        $pricePerDay = self::ALLOWED_STRIPE_PLANS[$stripePlan]["price_per_day"];

        return round($pricePerMonth * $months + $pricePerDay * $days, 1) * 100;
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
     */
    public function handleInvoicePaymentSucceeded($payload)
    {
        /** @var \Symfony\Component\HttpFoundation\Response $response */
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
     */
    public function handleCustomerSubscriptionDeleted(array $payload)
    {
        parent::handleCustomerSubscriptionDeleted($payload);
        
        $user = $this->getUserByStripeId($payload["data"]["object"]["customer"]);

        if ($user) {
            Log::log("user_cancelled_premium", $user);

            // TODO: Send cancellation email
        }

        return new Response('Webhook Handled', 200);
    }
}