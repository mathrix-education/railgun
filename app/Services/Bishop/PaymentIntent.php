<?php

namespace App\Services\Bishop;

use Stripe\PaymentIntent as StripePaymentIntent;

class PaymentIntent extends Bishop
{
    protected $dates = [
        "created_at"
    ];


    /**
     * Serialize a payment intent.
     *
     * @param StripePaymentIntent $paymentIntent
     *
     * @return self
     */
    public static function from($paymentIntent)
    {
        if ($paymentIntent instanceof StripePaymentIntent) {
            return new self(self::fromStripe($paymentIntent));
        } else {
            return null;
        }
    }


    public static function fromStripe(StripePaymentIntent $paymentIntent)
    {
        return [
            "id" => $paymentIntent->id,
            "amount" => $paymentIntent->amount,
            "client_secret" => $paymentIntent->client_secret,
            "created_at" => $paymentIntent->created,
            "status" => $paymentIntent->status
        ];
    }
}
