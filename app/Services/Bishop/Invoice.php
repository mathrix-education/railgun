<?php

namespace App\Services\Bishop;

use Stripe\Invoice as StripeInvoice;
use Stripe\PaymentIntent as StripePaymentIntent;

class Invoice extends Bishop
{
    protected $dates = [
        "period_end",
        "period_start"
    ];


    /**
     * Serialize an invoice.
     *
     * @param StripeInvoice $invoice
     *
     * @return self
     */
    public static function from($invoice)
    {
        if ($invoice instanceof StripeInvoice) {
            return new self(self::fromStripe($invoice));
        } else {
            return null;
        }
    }


    public static function fromStripe(StripeInvoice $invoice)
    {
        return [
            "id" => $invoice->id,
            "amount_due" => $invoice->amount_due,
            "amount_paid" => $invoice->amount_paid,
            "hosted_invoice_url" => $invoice->hosted_invoice_url,
            "invoice_pdf" => $invoice->invoice_pdf,
            "payment_intent" => $invoice->payment_intent instanceof StripePaymentIntent
                ? PaymentIntent::from($invoice->payment_intent) : $invoice->payment_intent,
            "period_end" => $invoice->period_end,
            "period_start" => $invoice->period_start,
            "status" => $invoice->status,
            "subscription" => $invoice->subscription
        ];
    }
}
