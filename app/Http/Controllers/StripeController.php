<?php

namespace App\Http\Controllers;

use Laravel\Cashier\Http\Controllers\WebhookController as CashierController;
use Mathrix\Lumen\Http\HttpJsonResponseBuilderTrait;

/**
 * Class StripeController
 *
 * @package App\Http\Controllers
 */
class StripeController extends CashierController
{
    use HttpJsonResponseBuilderTrait;


    /**
     * Handle a Stripe webhook.
     *
     * @param  array $payload
     */
    public function handleInvoicePaymentSucceeded($payload)
    {
        $this->http200Success($payload);
    }
}