<?php

declare(strict_types=1);

namespace Railgun\GraphQL\Mutations;

use Illuminate\Contracts\Bus\Dispatcher;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Railgun\Billing\Checkout\Cart;
use Railgun\Billing\Checkout\Checkout;
use Railgun\Jobs\DeliverProducts;
use Railgun\Models\User;
use Stripe\Invoice;

class CheckoutCart
{
    private Dispatcher $dispatcher;

    public function __construct(Dispatcher $dispatcher)
    {
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
        $cart = new Cart($args['cart']);
        /** @var User $user */
        $user     = $context->user();
        $checkout = new Checkout($user, $cart);

        $checkout->pay($args['paymentMethodId']);

        $invoice = $checkout->getInvoice();

        if ($invoice->status === Invoice::STATUS_PAID) {
            $this->dispatcher->dispatchNow(new DeliverProducts($invoice));
        }

        return $invoice;
    }
}
