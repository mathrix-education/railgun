<?php

declare(strict_types=1);

namespace Railgun\GraphQL\Mutations;

use Illuminate\Support\Collection;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Railgun\Billing\Cart\Cart;
use Railgun\Billing\Models\Premium;
use Railgun\Jobs\DeliverProducts;
use Railgun\Models\Pack;
use Railgun\Models\Training;
use Railgun\Models\User;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\CardException;
use Stripe\Invoice;
use Stripe\InvoiceItem;
use function class_basename;
use function dispatch_now;
use function strtolower;

class CheckoutCart
{
    /**
     * @var Collection|InvoiceItem[] $invoiceItems The created invoice items. This proper is used to cancel all the
     *      upcoming invoice items if an exception is thrown.
     */
    private Collection $invoiceItems;

    public function __construct()
    {
        $this->invoiceItems = new Collection();
    }

    /**
     * @param null           $rootValue The parent value, always null in this case.
     * @param mixed[]        $args      The mutation arguments.
     * @param GraphQLContext $context   The GraphQL context which holds the user.
     *
     * @return Invoice The generated invoice.
     *
     * @throws ApiErrorException
     */
    public function __invoke($rootValue, array $args, GraphQLContext $context): Invoice
    {
        // Setup the customer
        $paymentMethodId = $args['paymentMethodId'];
        /** @var User $user */
        $user = $context->user();
        $user->createCustomer($paymentMethodId);

        // Validate the cart for the current customer
        $cart = (new Cart($args['cart'], $user))->validate();

        try {
            // Create the invoice
            $invoice = $this->createInvoice($cart, $paymentMethodId);

            // The invoice is now ready to be paid
            $invoice = $this->pay($invoice);
        } catch (ApiErrorException $exception) {
            // If a exception is thrown, cancel all the invoice items.
            $this->invoiceItems->each(static fn(InvoiceItem $item) => $item->delete());

            if (isset($invoice)) {
                $invoice->delete(); // Also delete the invoice if it was created
            }

            throw $exception; // Rethrow the exception
        }

        if ($invoice->status === Invoice::STATUS_PAID) {
            dispatch_now(new DeliverProducts($invoice));
        }

        return $invoice;
    }

    /**
     * Add a product to the upcoming invoice.
     *
     * @param User                  $user    The user who owns the invoice.
     * @param Pack|Training|Premium $product The product to add to the existing invoice.
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
                'type'         => 'cart_v7',
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

        $this->invoiceItems->push(InvoiceItem::create($payload));
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
            'description'            => 'Achat de formations (mathrix.fr)',
            'metadata'               => [
                'type'    => Cart::IDENTIFIER,
                'user_id' => $cart->user->id,
            ],
            'default_payment_method' => $paymentMethodId,
        ]);
    }

    /**
     * Pay the created invoice.
     *
     * @param Invoice $invoice The invoice to pay.
     *
     * @return Invoice The paid invoice.
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
