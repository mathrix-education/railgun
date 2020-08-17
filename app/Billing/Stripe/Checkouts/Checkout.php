<?php

declare(strict_types=1);

namespace Railgun\Billing\Stripe\Checkouts;

use Illuminate\Bus\Dispatcher;
use Railgun\Billing\Cart\Cart;
use Railgun\Models\User;
use Stripe\Customer;
use Stripe\Exception\ApiErrorException;
use Stripe\Invoice;
use Stripe\StripeClient;
use function app;

abstract class Checkout
{
    protected StripeClient $client;
    protected Dispatcher   $dispatcher;

    protected Cart   $cart;
    protected User   $user;
    protected string $paymentMethodId;

    protected Customer $customer;
    protected Invoice  $invoice;

    public function __construct(Cart $cart, User $user, string $paymentMethodId)
    {
        $this->client     = app()->make(StripeClient::class);
        $this->dispatcher = app()->make(Dispatcher::class);

        $this->cart            = $cart;
        $this->user            = $user;
        $this->paymentMethodId = $paymentMethodId;
    }

    /**
     * Setup the checkout (in this can, ensure that the user is a stripe customer).
     *
     * @return $this
     *
     * @throws ApiErrorException
     */
    public function setup(): self
    {
        $this->customer = $this->user->ensureStripeCustomer($this->paymentMethodId);

        return $this;
    }

    /**
     * Validate the checkout parameters.
     *
     * @return $this
     */
    public function validate(): self
    {
        $this->cart = $this->cart->validate();

        return $this;
    }

    /**
     * If any {@see ApiErrorException} occurs, handle the failure here.
     *
     * @param ApiErrorException $exception The API error exception.
     *
     * @return Checkout
     *
     * @throws ApiErrorException
     */
    public function abort(ApiErrorException $exception): self
    {
        throw $exception;
    }

    /**
     * Build the invoice by calling the Stripe API.
     * The customer is billed here.
     *
     * @return $this
     *
     * @throws ApiErrorException
     */
    abstract public function build(): self;

    /**
     * After a successful invoice build, execute the callbacks to deliver products to the recipient.
     *
     * @return $this
     */
    abstract public function finalize(): self;

    /**
     * Run the checkout pipeline.
     *
     * @return $this
     *
     * @throws ApiErrorException
     */
    public function execute(): self
    {
        try {
            return $this->setup()
                ->validate()
                ->build()
                ->finalize();
        } catch (ApiErrorException $exception) {
            return $this->abort($exception);
        }
    }

    /**
     * @return Invoice the checkout attached invoice.
     */
    public function getInvoice(): Invoice
    {
        return $this->invoice;
    }
}
