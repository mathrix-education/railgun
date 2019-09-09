<?php

declare(strict_types=1);

namespace App\Services\Bishop;

use Stripe\PaymentIntent as StripePaymentIntent;

class PaymentIntent extends Bishop
{
    protected $dates = ['created_at'];

    /**
     * Serialize a payment intent.
     */
    public static function from(StripePaymentIntent $paymentIntent) : self
    {
        if ($paymentIntent instanceof StripePaymentIntent) {
            return new self(self::fromStripe($paymentIntent));
        }

        return null;
    }

    public static function fromStripe(StripePaymentIntent $paymentIntent)
    {
        return [
            'id' => $paymentIntent->id,
            'amount' => $paymentIntent->amount,
            'client_secret' => $paymentIntent->client_secret,
            'created_at' => $paymentIntent->created,
            'status' => $paymentIntent->status,
        ];
    }
}
