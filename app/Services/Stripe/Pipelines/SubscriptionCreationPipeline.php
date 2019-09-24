<?php

declare(strict_types=1);

namespace App\Services\Stripe\Pipelines;

use App\Models\Product;
use App\Services\Stripe\Exceptions\InvalidCoupon;
use App\Services\Stripe\SubscriptionUtils;
use Exception;
use Mathrix\Lumen\Zero\Exceptions\ValidationException;
use Stripe\Coupon;
use Stripe\Error\InvalidRequest;
use Stripe\Invoice;
use Stripe\InvoiceItem;
use Stripe\Subscription;
use function implode;
use function trans;

class SubscriptionCreationPipeline extends Pipeline
{
    private const ANNUALLY_TRAININGS_DISCOUNT = -2500;

    /** @var string The subscription plan */
    public $plan;
    /** @var Product The product (training) to buy with the subscription. */
    public $product;
    /** @var Subscription $subscription */
    public $subscription;
    /** @var Invoice $invoice */
    public $invoice;

    /**
     * @throws InvalidCoupon
     * @throws ValidationException
     */
    protected function initialize(): void
    {
        $this->validate(
            $this->request,
            [
                'plan'       => 'required|in:' . implode(',', SubscriptionUtils::PLANS),
                'product_id' => 'sometimes|required|exists:products,id',
            ]
        );

        $this->plan = $this->request->json('plan');

        if ($this->request->has('product_id')) {
            $this->product = Product::query()
                ->findOrFail($this->request->json('product_id'));
        }

        if ($this->user->isStripeCustomer()) {
            return;
        }

        (new CustomerCreationPipeline())->flow();
    }

    /**
     * @throws Exception
     */
    protected function exchange(): void
    {
        if ($this->product !== null) {
            // If there is a product select, create the related invoice item.
            $productInvoiceItem = InvoiceItem::create([
                'currency'    => 'eur',
                'customer'    => $this->user->stripe_id,
                'description' => $this->product->name,
                'metadata'    => [
                    'user_id'    => $this->user->id,
                    'product_id' => $this->product->id,
                ],
                'quantity'    => 1,
                'unit_amount' => $this->product->price,
            ]);

            if ($this->plan === SubscriptionUtils::ANNUALLY_PLAN) {
                // If the subscription is an annual plan, apply a discount to the invoice.
                $discountInvoiceItem = InvoiceItem::create([
                    'currency'    => 'eur',
                    'customer'    => $this->user->stripe_id,
                    'description' => trans('stripe.discount.annual_subscription'),
                    'metadata'    => [
                        'user_id' => $this->user->id,
                    ],
                    'quantity'    => 1,
                    'unit_amount' => self::ANNUALLY_TRAININGS_DISCOUNT,
                ]);
            }
        }

        // Create the initial payload
        $payload = [
            'customer'       => $this->user->stripe_id,
            'default_source' => $this->request->json('card.id')
                ?? $this->user->asStripeCustomer()->default_source,
            'items'          => [
                ['plan' => $this->plan],
            ],
            'metadata'       => [
                'user_id' => $this->user->id,
            ],
            'expand'         => ['latest_invoice.payment_intent'],
        ];

        if (SubscriptionUtils::isPlanProrated($this->plan)) {
            // Add the billing cycle anchor if necessary
            $payload['billing_cycle_anchor'] = SubscriptionUtils::getProrationAnchor($this->plan)
                ->getTimestamp();
        }

        if ($this->product !== null) {
            // Add the product to the subscriptions metadata if necessary
            $payload['metadata']['product_id'] = $this->product->id;
        }

        try {
            $this->subscription = Subscription::create($payload);
        } catch (Exception $exception) {
            isset($productInvoiceItem) && $productInvoiceItem->delete();
            isset($discountInvoiceItem) && $discountInvoiceItem->delete();
            throw $exception;
        }
    }

    protected function finalize(): void
    {
        $this->user->invalidate();
    }
}
