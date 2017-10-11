<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Laravel\Cashier\Http\Controllers\WebhookController as CashierController;

/**
 * Class StripeController
 *
 * @package App\Http\Controllers
 */
class StripeController extends CashierController
{
    /**
     * @apiVersion 1.0.0
     * @api {post} /stripe/subscribe Subscribe
     * @apiGroup Stripe
     * @apiName StripeSubscribe
     * @apiDescription Subscribe user to plan
     * @apiPermission registered
     * @apiUse Bearer
     * @apiParam (Data) {string} stripe_token the stripe token
     * @apiParam (Data) {string=v3_annually,v3_monthly} stripe_plan the stripe plan
     * @apiParam (Data) {string} [stripe_coupon=null] the stripe coupon
     *
     * @param Request $request
     *
     * @return Response
     */
    public function subscribe(Request $request)
    {
        $stripeToken = $request->input('stripe_token');
        $stripeCoupon = $request->input('stripe_coupon');

        /** @var User $user */
        $user = $request->getUser();

        $user->newSubscription('main', 'premium')
            ->withCoupon($stripeCoupon)
            ->create($stripeToken);

        return response();
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
     * @return Response
     */
    public function handleInvoicePaymentSucceeded($payload)
    {
        return new Response('Webhook Handled', 200);
    }
}