<?php

declare(strict_types=1);

namespace Railgun\Billing\Checkout;

use ArrayAccess;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Railgun\Billing\Contracts\Sellable;
use Railgun\Billing\Products\Premium;
use Railgun\Models\User;
use Railgun\Support\ArrayAccessImmutable;
use Stripe\Customer;
use Stripe\ErrorObject;
use Stripe\Exception\ApiErrorException;
use Stripe\Invoice;
use Stripe\InvoiceItem;
use Stripe\PaymentIntent;
use Stripe\StripeClient;
use function app;
use function class_basename;
use function config;
use function sprintf;
use function strtolower;
use function trans_choice;
use function ucfirst;

class Checkout implements ArrayAccess
{
    use ArrayAccessImmutable;

    private StripeClient $stripe;
    /** @var Collection|InvoiceItem[] $invoiceItems */
    private Collection   $invoiceItems;
    private Invoice      $invoice;
    private User         $user;
    private Customer     $customer;
    private Cart         $cart;

    public function __construct(User $user, Cart $cart)
    {
        $this->stripe       = app()->make(StripeClient::class);
        $this->invoiceItems = new Collection();
        $this->user         = $user;
        $this->cart         = $cart;
    }

    /**
     * @return Invoice The Stripe Invoice.
     */
    public function getInvoice(): Invoice
    {
        if (!$this->invoice->payment_intent instanceof PaymentIntent) {
            $this->invoice = $this->stripe->invoices->retrieve(
                $this->invoice->id,
                ['expand' => ['payment_intent']],
            );
        }

        return $this->invoice;
    }

    /**
     * @throws ApiErrorException
     */
    protected function build(): void
    {
        if (isset($this->invoice)) {
            return;
        }

        $this->invoiceItems = $this->cart->getProducts()->map(function (Sellable $product): void {
            $type    = strtolower(class_basename($product));
            $payload = [
                'customer'    => $this->customer->id,
                'description' => sprintf(
                    '%s : "%s"',
                    ucfirst(trans_choice("models.{$type}", 1)),
                    $product->name,
                ),
                'price'       => $product->stripe_price_id,
                'metadata'    => [
                    'user_id'      => $this->user->id,
                    'product_type' => $type,
                    'product_id'   => $product->id,
                ],
            ];

            $this->stripe->invoiceItems->create($payload);
        });

        if ($this->cart->withPremium()) {
            $this->invoiceItems->push($this->stripe->invoiceItems->create([
                'customer'    => $this->customer->id,
                'currency'    => 'eur',
                'amount'      => Premium::PRICE_NO_SUB,
                'description' => Premium::PRODUCT['name'],
                'period'      => [
                    'start' => Carbon::now()->timestamp,
                    'end'   => Premium::getAnchor()->timestamp,
                ],
                'metadata'    => [
                    'user_id'      => $this->user->id,
                    'product_type' => 'premium',
                ],
            ]));
        }

        if ($this->cart->getCoupon()) {
            $this->invoiceItems->push($this->stripe->invoiceItems->create([
                'customer'    => $this->customer->id,
                'price'       => $this->cart->getCouponAmount(),
                'description' => sprintf(
                    '%s : "%s"',
                    trans_choice('models.coupon', 1),
                    $this->cart->getCoupon()->id,
                ),
            ]));
        }

        $this->invoice = $this->stripe->invoices->create([
            'customer'    => $this->customer->id,
            'description' => 'Panier du ' . Carbon::now()->format('d/m/Y'),
            'metadata'    => [
                'type'    => 'cart_v7',
                'release' => config('app.release'),
            ],
        ]);
    }

    public function abort(): void
    {
        if (!isset($this->invoice) || $this->invoice->status === Invoice::STATUS_DRAFT) {
            $this->invoiceItems->each(function (InvoiceItem $invoiceItem): void {
                $this->stripe->invoiceItems->delete($invoiceItem->id);
            });
        }

        if (!isset($this->invoice)) {
            return;
        }

        if ($this->invoice->status === Invoice::STATUS_DRAFT) {
            $this->stripe->invoices->delete($this->invoice->id);
        } else {
            $this->stripe->invoices->voidInvoice($this->invoice->id);
        }
    }

    /**
     * @param string $paymentMethod The Stripe payment method.
     *
     * @throws ApiErrorException
     */
    public function pay(string $paymentMethod): void
    {
        if ($this->user->isStripeCustomer()) {
            $paymentMethod = $this->user->attachPaymentMethod($paymentMethod)->id;
            /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
            $this->customer = $this->user->asStripeCustomer();
        } else {
            $this->customer = $this->user->createCustomer($paymentMethod);
        }

        try {
            $this->build();

            $this->invoice = $this->stripe->invoices->finalizeInvoice($this->invoice->id, [
                'expand' => ['payment_intent'],
            ]);

            if ($this->invoice->paid) {
                // The invoice has already been paid, return immediately
                return;
            }

            $this->invoice = $this->stripe->invoices->pay($this->invoice->id, [
                'off_session'    => false,
                'payment_method' => $paymentMethod,
                'expand'         => ['payment_intent'],
            ]);
        } catch (ApiErrorException $exception) {
            $this->invoice = $this->stripe->invoices->retrieve($this->invoice->id, [
                'expand' => ['payment_intent'],
            ]);

            if ($exception->getError()->code !== ErrorObject::CODE_INVOICE_PAYMENT_INTENT_REQUIRES_ACTION) {
                $this->abort();
            }
        }
    }
}
