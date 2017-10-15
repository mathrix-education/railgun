<?php

namespace App\Http\Controllers;

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
    private const ALLOWED_STRIPE_PLANS = ['v3_annually', 'v3_monthly'];


    /**
     * @apiVersion 0.8.1
     * @api {post} /stripe/subscribe Subscribe
     * @apiGroup Stripe
     * @apiName StripeSubscribe
     * @apiDescription Subscribe user to plan
     * @apiPermission registered
     * @apiUse Bearer
     * @apiParam (Data) {string} stripe_token The stripe token
     * @apiParam (Data) {string=v3_annually,v3_monthly} stripe_plan The stripe plan
     * @apiParam (Data) {string} [stripe_coupon=null] The stripe coupon
     * @apiError (Error 401) invalid_request Invalid stripe_plan
     *
     * @param Request $request
     *
     * @return Response
     */
    public function subscribe(Request $request)
    {
        $stripeToken = $request->input('stripe_token');
        $stripePlan = $request->input('stripe_plan');
        $stripeCoupon = $request->input('stripe_coupon');

        if (!in_array($stripePlan, self::ALLOWED_STRIPE_PLANS)) {
            return response()->json([
                'error' => 'invalid_request',
                'message' => 'stripe_plan has to be in [' . implode(', ', self::ALLOWED_STRIPE_PLANS) . ']'
            ], 401);
        }

        /** @var User $user */
        $user = $request->user();

        $user->newSubscription('premium', $stripePlan)
            ->withCoupon($stripeCoupon)
            ->create($stripeToken);

        return response()->json(['status' => 'success']);
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
        // TODO: LOG IT
        return new Response('Webhook Handled', 200);
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
        // TODO: LOG IT
        return new Response('Webhook Handled', 200);
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
        $reponse = parent::handleCustomerSubscriptionDeleted($payload);

        return $reponse;
    }
}