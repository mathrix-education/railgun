<?php

declare(strict_types=1);

namespace Railgun\GraphQL\Mutations;

use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Railgun\Billing\Cart\Cart;
use Railgun\Billing\Stripe\Checkouts\Checkout as StripeCheckout;
use Railgun\Billing\Stripe\Checkouts\InvoiceCheckout;
use Railgun\Billing\Stripe\Checkouts\SubscriptionCheckout;
use Railgun\Models\User;
use Stripe\Exception\ApiErrorException;
use Stripe\Invoice;

class Checkout
{
    /**
     * @param null           $_       The parent value, always null in this case.
     * @param mixed[]        $args    The mutation arguments (cart: CartInput!, paymentMethodId: String!).
     * @param GraphQLContext $context The GraphQL context.
     *
     * @return Invoice The generated Stripe invoice.
     *
     * @throws ApiErrorException
     */
    public function __invoke($_, array $args, GraphQLContext $context): Invoice
    {
        /** @var User $user */
        $user            = $context->user();
        $paymentMethodId = $args['paymentMethodId'];

        $cart = Cart::build($args['cart'])->validate($user);

        /** @var StripeCheckout $checkout */
        $checkout = $cart->plan
            ? new SubscriptionCheckout($cart, $user, $paymentMethodId)
            : new InvoiceCheckout($cart, $user, $paymentMethodId);

        return $checkout->execute()->getInvoice();
    }
}
