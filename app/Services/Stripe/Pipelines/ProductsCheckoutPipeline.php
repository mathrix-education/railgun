<?php

declare(strict_types=1);

namespace App\Services\Stripe\Pipelines;

use App\Models\Product;
use App\Services\Stripe\Exceptions\ProductAlreadyBought;
use App\Services\Stripe\Utils\InvoiceUtils;
use Mathrix\Lumen\Zero\Exceptions\ValidationException;
use Stripe\Error\Card as CardException;
use Stripe\Invoice as StripeInvoice;
use Stripe\InvoiceItem as StripeInvoiceItem;

/**
 * Create a new invoice.
 */
class ProductsCheckoutPipeline extends Pipeline
{
    /** @var string The card id. */
    private $cardId;
    /** @var Product $product */
    public $product;
    /** @var StripeInvoice $invoice */
    public $invoice;

    /**
     * @throws ProductAlreadyBought
     * @throws ValidationException
     */
    protected function initialize(): void
    {
        $this->validate(
            $this->request,
            [
                'product_id' => 'required|exists:products,id',
                'card.id'    => 'required_without:card.token',
                'card.token' => 'required_without:card.id',
            ]
        );

        $this->product = Product::query()
            ->findOrFail($this->request->json('product_id'));

        if ($this->user->hasPurchased($this->product)) {
            throw new ProductAlreadyBought($this->product);
        }

        if (!$this->user->isStripeCustomer()) {
            (new CustomerCreationPipeline())->flow();
            $this->cardId = $this->user->asStripeCustomer()->default_source;
        } elseif ($this->request->has('card.token')) {
            $this->cardId = $this->user->addSource((string)$this->request->json('card.token'))->id;
        } else {
            $this->cardId = (string)$this->request->json('card.id');
        }
    }

    /**
     * @throws CardException
     */
    protected function exchange(): void
    {
        $invoiceItem   = StripeInvoiceItem::create(InvoiceUtils::toInvoiceItemPayload($this->product));
        $this->invoice = StripeInvoice::create([
            'customer'       => $this->user->stripe_id,
            'default_source' => $this->cardId,
            'metadata'       => [
                'user_id' => $this->user->id,
            ],
        ]);

        try {
            $this->invoice->pay([
                'off_session' => false,
                'source'      => $this->cardId,
                'expand'      => ['payment_intent'],
            ]);
        } catch (CardException $exception) {
            $body  = $exception->getJsonBody();
            $error = $body['error']['code'] ?? null;

            if ($error !== 'invoice_payment_intent_requires_action') {
                throw $exception;
            }

            // Refresh the invoice
            $this->invoice = StripeInvoice::retrieve([
                'id'     => $this->invoice->id,
                'expand' => ['payment_intent'],
            ]);
        }
    }

    protected function finalize(): void
    {
        // TODO: Implement finalize() method.
    }
}
