<?php

declare(strict_types=1);

namespace Railgun\GraphQL\Mutations;

use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Railgun\Billing\Cart\Cart;
use Railgun\Billing\Cart\DeliverProducts;
use Railgun\Billing\Models\Premium;
use Railgun\GraphQL\Traits\SetupCustomer;
use Railgun\Models\Pack;
use Railgun\Models\Training;
use Railgun\Models\User;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\CardException;
use Stripe\Invoice;
use Stripe\InvoiceItem;
use Stripe\PaymentIntent;
use function class_basename;
use function strtolower;

class CheckoutCart
{
    use SetupCustomer;

    /**
     * Return a value for the field.
     *
     * @param null           $rootValue
     * @param mixed[]        $args
     * @param GraphQLContext $context
     * @param ResolveInfo    $resolveInfo
     *
     * @return bool
     *
     * @throws ApiErrorException
     */
    public function __invoke($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        // Setup the customer
        $paymentMethodId = $args['paymentMethodId'];
        /** @noinspection PhpParamsInspection */
        $user = $this->setupCustomer($context->user(), $paymentMethodId);

        // Validate the cart for teh customer
        $cart = (new Cart($args['cart'], $user))->validate();

        // Create the invoice
        $invoice = $this->createInvoice($cart, $paymentMethodId);

        // The invoice is now ready to be paid
        $invoice = $this->pay($invoice);

        if ($invoice->status === Invoice::STATUS_PAID) {
            (new DeliverProducts($invoice))->deliver();
        }

        $data = $invoice->jsonSerialize();

        if ($invoice->payment_intent->status === PaymentIntent::STATUS_REQUIRES_ACTION) {
            $data += ['client_secret' => $invoice->payment_intent->client_secret];
        }

        return $data;
    }

    /**
     * @param User                  $user
     * @param Pack|Training|Premium $product
     *
     * @throws ApiErrorException
     */
    private function addItemToInvoice(User $user, $product): void
    {
        $payload = [
            'currency'    => 'eur',
            'customer'    => $user->stripe_id,
            'description' => $product->name,
            'metadata'    => [
                'user_id'      => $user->id,
                'product_id'   => $product->id,
                'product_type' => strtolower(class_basename($product)),
            ],
            'quantity'    => 1,
            'unit_amount' => $product->price,
        ];

        if ($product instanceof Premium) {
            $payload['metadata']['end_date'] = $product->end_date->getTimestamp();
        }

        InvoiceItem::create($payload);
    }

    /**
     * Create a {@see Invoice} representing the current cart.
     *
     * @param Cart   $cart            The validated cart.
     * @param string $paymentMethodId The payment method id.
     *
     * @return Invoice The create invoice, ready to be paid.
     *
     * @throws ApiErrorException
     */
    private function createInvoice(Cart $cart, string $paymentMethodId): Invoice
    {
        $cart->packs->each(fn(Pack $pack) => $this->addItemToInvoice($cart->user, $pack));
        $cart->trainings->each(fn(Training $training) => $this->addItemToInvoice($cart->user, $training));

        if ($cart->premium !== null) {
            $this->addItemToInvoice($cart->user, $cart->premium);
        }

        return Invoice::create([
            'customer'               => $cart->user->stripe_id,
            'description'            => 'Achat formation BOBO', // TODO: change this
            'metadata'               => ['user_id' => $cart->user->id],
            'default_payment_method' => $paymentMethodId,
        ]);
    }

    /**
     * Pay the created invoice.
     *
     * @param Invoice $invoice
     *
     * @return Invoice
     *
     * @throws ApiErrorException
     * @throws CardException
     *
     * @noinspection PhpRedundantCatchClauseInspection
     * @noinspection CallableParameterUseCaseInTypeContextInspection
     */
    private function pay(Invoice $invoice): Invoice
    {
        try {
            $invoice->pay([
                'off_session' => false,
                'expand'      => ['payment_intent'],
            ]);
        } catch (CardException $exception) {
            $body  = $exception->getJsonBody();
            $error = $body['error']['code'] ?? null;

            if ($error !== 'invoice_payment_intent_requires_action') {
                // We only handle 3DS2 errors here
                throw $exception;
            }

            // Refresh the invoice
            if (!isset($invoice->payment_intent->client_secret)) {
                $invoice = Invoice::retrieve([
                    'id'     => $invoice->id,
                    'expand' => ['payment_intent'],
                ]);
            }
        }

        return $invoice;
    }
}
