<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Laravel\Cashier\Http\Controllers\WebhookController as CashierController;

/**
 * Class StripeController
 *
 * @package App\Http\Controllers
 */
class StripeController extends CashierController
{
    /**
     * Handle a Stripe webhook.
     *
     * @param  array $payload
     *
     * @return JsonResponse
     */
    public function handleInvoicePaymentSucceeded($payload)
    {
        die('lol');
        // Grant user user premium
        return response()->json($payload);
    }
}