<?php

declare(strict_types=1);

namespace Railgun\GraphQL\Mutations;

use Carbon\Carbon;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Railgun\Billing\Cart\Cart;
use Railgun\Billing\Stripe\Contracts\StripeProduct;
use Railgun\Jobs\DeliverProducts;
use Railgun\Models\User;
use Stripe\Customer;
use Stripe\ErrorObject;
use Stripe\Exception\ApiErrorException;
use Stripe\Invoice;
use Stripe\InvoiceItem;
use Stripe\StripeClient;
use function class_basename;
use function config;
use function sprintf;
use function trans_choice;
use function ucfirst;

class CheckoutCart
{
    protected StripeClient $client;
    protected Dispatcher   $dispatcher;

    protected User   $user;
    protected Cart   $cart;
    protected string $paymentMethodId;

    protected Customer   $customer;
    protected Invoice    $invoice;
    /** @var Collection|array<int, InvoiceItem> $invoiceItems The Stripe InvoiceItems */
    protected Collection $invoiceItems;

    public function __construct(StripeClient $client, Dispatcher $dispatcher)
    {
        $this->client     = $client;
        $this->dispatcher = $dispatcher;
    }

    /**
     * @param null           $_       The parent value, always null in this case.
     * @param mixed[]        $args    The mutation arguments.
     * @param GraphQLContext $context The GraphQL context which holds the user.
     *
     * @return Invoice The generated invoice.
     *
     * @throws ApiErrorException
     */
    public function __invoke($_, array $args, GraphQLContext $context): Invoice
    {
        try {
            $this->setup($args, $context);
            $this->buildInvoice();
            $this->payInvoice();
            $this->deliverProducts();
        } catch (ApiErrorException $exception) {
            $this->abort();
        }

        return $this->invoice;
    }

    /**
     * Abort the checkout, delete the invoice if possible.
     *
     * @throws ApiErrorException
     */
    protected function abort(): void
    {
        if (!isset($this->invoice) || $this->invoice->status === Invoice::STATUS_DRAFT) {
            $this->invoiceItems->each(function (InvoiceItem $invoiceItem): void {
                $this->client->invoiceItems->delete($invoiceItem->id);
            });
        }

        if (!isset($this->invoice)) {
            return;
        }

        if ($this->invoice->status === Invoice::STATUS_DRAFT) {
            $this->client->invoices->delete($this->invoice->id);
        } else {
            $this->client->invoices->voidInvoice($this->invoice->id);
        }
    }

    /**
     * Stage 1: setup the Stripe customer so the user is able to purchase the products.
     *
     * @param array<string, mixed> $args    The mutation arguments.
     * @param GraphQLContext       $context The GraphQL context which holds the user.
     *
     * @throws ApiErrorException
     */
    protected function setup(array $args, GraphQLContext $context): void
    {
        /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
        $this->user            = $context->user();
        $this->cart            = Cart::build($args['cart'])->validate();
        $this->paymentMethodId = $args['paymentMethodId'];
        $this->invoiceItems    = new Collection();

        if (!$this->user->stripe_id) {
            $this->customer = $this->user->createStripeCustomer($this->paymentMethodId);
        } else {
            $this->customer = $this->user->updateStripeCustomer($this->paymentMethodId);
        }
    }

    /**
     * Stage 2: build the invoice.
     *
     * @throws ApiErrorException
     */
    protected function buildInvoice(): void
    {
        $this->invoiceItems = $this->cart->products->map(function (StripeProduct $product): void {
            $type = Str::lower(class_basename($product));

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

            $this->client->invoiceItems->create($payload);
        });

        if ($this->cart->coupon) {
            $this->invoiceItems->push($this->client->invoiceItems->create([
                'customer'    => $this->customer->id,
                'price'       => $this->cart->coupon,
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
            'metadata'    => [
                'type'    => Cart::IDENTIFIER,
                'release' => config('app.release'),
            ],
        ]);
    }

    /**
     * Stage 3: pay the generated invoice.
     *
     * @throws ApiErrorException
     */
    protected function payInvoice(): void
    {
        try {
            $this->invoice = $this->client->invoices->finalizeInvoice($this->invoice->id, [
                'expand' => ['payment_intent'],
            ]);

            if ($this->invoice->status === Invoice::STATUS_PAID) {
                // The invoice has already been paid using the default payment method, return immediately
                return;
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
                // invoice_payment_intent_requires_action is considered as a "valid" response
                return;
            }

            throw $exception;
        }
    }

    /**
     * Stage 4: deliver the product to the user if the invoice has been paid.
     */
    protected function deliverProducts(): void
    {
        if ($this->invoice->status === Invoice::STATUS_PAID) {
            $this->dispatcher->dispatchNow(new DeliverProducts($this->invoice));
        }
    }
}
