<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Laravel\Cashier\Http\Controllers\WebhookController as CashierController;
use Mathrix\Lumen\Http\HttpJsonResponseBuilderTrait;

/**
 * Class StripeController
 *
 * @package App\Http\Controllers
 */
class StripeController extends CashierController
{
    use HttpJsonResponseBuilderTrait {
        __call as buildHttpResponse;
    }


    /**
     * Handle a Stripe webhook.
     *
     * @param  array $payload
     *
     * @return JsonResponse
     */
    public function handleInvoicePaymentSucceeded($payload)
    {
        return $this->buildHttpResponse('http200Success', $payload);
    }
}