<?php
namespace App\Http\Controllers;

use App\Exceptions\Http400InvalidRequestException;
use App\Models\Log;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Laravel\Cashier\Http\Controllers\WebhookController as CashierController;

/**
 * Class StripeController
 *
 * @author Mathrix Back-End Team
 * @package App\Http\Controllers
 * @since 0.8.1
 * @copyright Mathrix
 */
class StripeController extends CashierController
{
    private const ALLOWED_STRIPE_PLANS = ["v3_annually", "v3_monthly"];


    /**
     * @apiVersion 0.8.1
     * @api {post} /stripe/subscribe Subscribe
     * @apiGroup Stripe
     * @apiName StripeSubscribe
     * @apiDescription Subscribe user to plan
     * @apiPermission registered
     * @apiUse Bearer
     * @apiParam (Data) {string} [stripe_token] The stripe token; can be null if a coupon is given
     * @apiParam (Data) {string=v3_annually,v3_monthly} stripe_plan The stripe plan
     * @apiParam (Data) {string} [stripe_coupon=null] The stripe coupon
     * @apiError (Error 400) invalid_request stripe_token and stripe_coupon cannot be null at the same time
     * @apiError (Error 400) invalid_request stripe_plan has to be in the allowed stripe plans
     *
     * @param Request $request
     *
     * @return Response
     * @throws Http400InvalidRequestException
     */
    public function subscribe(Request $request)
    {
        $stripeToken = $request->input("stripe_token");
        $stripePlan = $request->input("stripe_plan");
        $stripeCoupon = $request->input("stripe_coupon");

        if ($stripeToken === null && $stripeCoupon === null) {
            throw new Http400InvalidRequestException("'stripe_token' and 'stripe_coupon' cannot be both null");
        }

        if (!in_array($stripePlan, self::ALLOWED_STRIPE_PLANS)) {
            throw new Http400InvalidRequestException("stripe_plan has to be in ['" .
                implode("', '", self::ALLOWED_STRIPE_PLANS) . "']");
        }

        /** @var User $user */
        $user = $request->user();

        $user->newSubscription("premium", $stripePlan)
            ->withCoupon($stripeCoupon)
            ->create($stripeToken);

        Log::log("user_paid_for_premium", $user);

        return response()->json(["status" => "success"]);
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
     */
    public function handleInvoicePaymentSucceeded($payload)
    {
        /** @var \Symfony\Component\HttpFoundation\Response $response */
        $response = parent::handleInvoicePaymentSucceeded($payload);

        if ($response->getStatusCode() == 200) {
            $user = $this->getUserByStripeId($payload['data']['object']['customer']);

            if ($user) {
                Log::log("user_payment_succeed_for_premium", $user);
            }
        }

        return $response;
    }


    /**
     *
     * Payment succeeded
     *
     * @param  array $payload
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handleInvoicePaymentFailed(array $payload)
    {
        /** @var \Symfony\Component\HttpFoundation\Response $response */
        $response = parent::handleInvoicePaymentFailed($payload);

        if ($response->getStatusCode() == 200) {
            $user = $this->getUserByStripeId($payload['data']['object']['customer']);

            if ($user) {
                Log::log("user_payment_failed_for_premium", $user);

                // TODO: Send payment failed email
            }
        }

        return $response;
    }


    /**
     * Handle customer.subscription.deleted
     *
     * @param array $payload
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handleCustomerSubscriptionDeleted(array $payload)
    {
        /** @var \Symfony\Component\HttpFoundation\Response $response */
        $response = parent::handleCustomerSubscriptionDeleted($payload);

        if ($response->getStatusCode() == 200) {
            $user = $this->getUserByStripeId($payload['data']['object']['customer']);

            if ($user) {
                Log::log("user_cancelled_premium", $user);

                // TODO: Send cancellation email
            }
        }

        return $response;
    }
}