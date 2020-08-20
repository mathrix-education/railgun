<?php

declare(strict_types=1);

namespace Railgun\Billing\Stripe\Checkouts;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Railgun\Billing\Stripe\Contracts\StripeSellable;
use Railgun\Billing\Stripe\Exceptions\ThreeDSecureException;
use Railgun\Jobs\DispatchCart;
use Stripe\ErrorObject;
use Stripe\Exception\ApiErrorException;
use Stripe\Invoice;
use Stripe\InvoiceItem;
use function class_basename;
use function sprintf;
use function trans_choice;
use function ucfirst;

/**
 * Used when the user wants to buy products without subscribing to {@see ModeMachine}.
 */
class InvoiceCheckout extends Checkout
{
    /** @var Collection|InvoiceItem[] $invoiceItems The invoice items. */
    protected Collection $invoiceItems;

    /**
     * Abort the checkout, delete the invoice if possible.
     *
     * @param ApiErrorException $exception The API error exception.
     *
     * @return InvoiceCheckout
     *
     * @throws ApiErrorException
     */
    public function abort(ApiErrorException $exception): self
    {
        if (!isset($this->invoice)) {
            throw $exception;
        }

        if ($this->invoice->status === Invoice::STATUS_DRAFT) {
            // @codeCoverageIgnoreStart
            // Invoice is still in draft status and thus can be completely deleted, but it should never be the case
            $this->invoiceItems->each(function (InvoiceItem $invoiceItem): void {
                $this->client->invoiceItems->delete($invoiceItem->id);
            });

            $this->client->invoices->delete($this->invoice->id);
            // @codeCoverageIgnoreEnd
        } else {
            // Invoice has already advanced to open or further and can only be voided
            $this->client->invoices->voidInvoice($this->invoice->id);
        }

        throw $exception;
    }

    /**
     * @return $this
     *
     * @throws ApiErrorException
     */
    public function build(): self
    {
        $this->invoiceItems = $this->cart->products->map(function (StripeSellable $product): void {
            $type = Str::lower(class_basename($product));

            $payload = [
                'customer'    => $this->customer->id,
                'description' => sprintf(
                    '%s : "%s"',
                    ucfirst(trans_choice("models.{$type}", 1)),
                    $product->name,
                ),
                'price'       => $product->stripe_price_id,
            ];

            $this->client->invoiceItems->create($payload);
        });

        if ($this->cart->coupon) {
            $this->invoiceItems->push($this->client->invoiceItems->create([
                'customer'    => $this->customer->id,
                'currency'    => 'eur',
                'amount'      => -$this->cart->couponAmount,
                'description' => sprintf(
                    '%s : "%s"',
                    trans_choice('models.coupon', 1),
                    $this->cart->coupon->id,
                ),
            ]));
        }

        $this->invoice = $this->client->invoices->create([
            'customer'    => $this->customer->id,
            'description' => 'Panier du ' . Carbon::now()->format('d/m/Y'),
            'metadata'    => $this->cart->toMetadata(),
        ]);

        try {
            $this->invoice = $this->client->invoices->finalizeInvoice($this->invoice->id, [
                'expand' => ['payment_intent'],
            ]);

            if ($this->invoice->status === Invoice::STATUS_PAID) {
                // The invoice has already been paid using the default payment method, return immediately (but it
                // should never happen)
                return $this; // @codeCoverageIgnore
            }

            $this->invoice = $this->client->invoices->pay($this->invoice->id, [
                'off_session'    => false,
                'payment_method' => $this->paymentMethodId,
                'expand'         => ['payment_intent'],
            ]);
        } catch (ApiErrorException $exception) {
            $this->invoice = $this->client->invoices->retrieve($this->invoice->id, [
                'expand' => ['payment_intent'],
            ]);

            if ($exception->getStripeCode() === ErrorObject::CODE_INVOICE_PAYMENT_INTENT_REQUIRES_ACTION) {
                throw new ThreeDSecureException($this->invoice->payment_intent);
            }

            throw $exception;
        }

        return $this;
    }

    public function finalize(): self
    {
        if ($this->invoice->status === Invoice::STATUS_PAID) {
            $this->dispatcher->dispatchNow(new DispatchCart($this->cart, $this->user->id, $this->invoice->id));
        }

        $this->user->flushCache();

        return $this;
    }
}
