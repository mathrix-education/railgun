<?php

declare(strict_types=1);

namespace App\Services\Bishop;

use Stripe\Invoice as StripeInvoice;
use Stripe\PaymentIntent as StripePaymentIntent;

/**
 * @property-read string               $id
 * @property-read int                  $amount_due
 * @property-read int                  $amount_paid
 * @property-read string               $hosted_invoice_url
 * @property-read string               $invoice_pdf
 * @property-read PaymentIntent|string $payment_intent
 * @property-read string               $period_end
 * @property-read string               $period_start
 * @property-read string               $status
 * @property-read Subscription|string  $subscription
 */
class Invoice extends Bishop
{
    protected $dates = [
        'period_end',
        'period_start',
    ];

    /**
     * Serialize an invoice.
     *
     * @param StripeInvoice $invoice
     *
     * @return Invoice
     */
    public static function from(StripeInvoice $invoice): self
    {
        return new self(self::fromStripe($invoice));
    }

    public static function fromStripe(StripeInvoice $invoice)
    {
        return [
            'id'                 => $invoice->id,
            'amount_due'         => $invoice->amount_due,
            'amount_paid'        => $invoice->amount_paid,
            'hosted_invoice_url' => $invoice->hosted_invoice_url,
            'invoice_pdf'        => $invoice->invoice_pdf,
            'payment_intent'     => $invoice->payment_intent instanceof StripePaymentIntent
                ? PaymentIntent::from($invoice->payment_intent) : $invoice->payment_intent,
            'period_end'         => $invoice->period_end,
            'period_start'       => $invoice->period_start,
            'status'             => $invoice->status,
            'subscription'       => $invoice->subscription,
        ];
    }
}
